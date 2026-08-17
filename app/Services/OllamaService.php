<?php

namespace App\Services;

use App\Models\TenantModule;
use App\Models\User;
use App\Services\AI\ToolRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $aiModel;

    public function __construct(protected ToolRegistry $toolRegistry)
    {
        $this->baseUrl = config('services.ollama.base_url');
        $this->apiKey = config('services.ollama.key');
        $this->aiModel = config('services.ollama.model');
    }

    /**
     * পাবলিক এন্ট্রি পয়েন্ট — Controller এখান থেকেই কল করবে।
     * $history সবসময় খালি দিয়ে শুরু হবে; recursive tool-call loop
     * নিজের ভেতরেই history সামলাবে (chatInternal দিয়ে)।
     */
    public function chat(string $message, User $user, ?string $tenantId = null, array $history = []): array
    {
        $systemPrompt = $this->buildSystemPrompt($user, $tenantId);
        $tools = $this->toolRegistry->toolsForUser($user, $tenantId);

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $message]]
        );

        return $this->chatInternal($messages, $tools, $user, $tenantId);
    }

    /**
     * এখানে $messages ইতিমধ্যেই সম্পূর্ণ (system + history + user message)।
     * tool_call এলে এখানেই নিজের ভেতরে loop করবে — বাইরের chat() কে
     * আর কল করবে না, তাই user message দ্বিতীয়বার যোগ হওয়ার সুযোগ নেই।
     */
    protected function chatInternal(array $messages, array $tools, User $user, ?string $tenantId, int $depth = 0): array
    {
        // অসীম লুপ ঠেকাতে একটা hard limit
        if ($depth > 5) {
            return ['message' => ['role' => 'assistant', 'content' => 'দুঃখিত, একটা সমস্যার কারণে উত্তর দিতে পারছি না।']];
        }

        $payload = [
            'model' => $this->aiModel,
            'messages' => $messages,
            'tools' => $tools,
            'stream' => false,
        ];

        Log::debug('Ollama request payload', ['payload' => $payload]);

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(60)
            ->retry(2, 1000)
            ->post("{$this->baseUrl}/chat", $payload);

        $data = $response->throw()->json();

        if (empty($data['message']['tool_calls'])) {
            return $data;
        }

        // tool_calls গুলোর arguments normalize করা (স্ট্রিং হলে array বানানো)
        $assistantMessage = $data['message'];
        foreach ($assistantMessage['tool_calls'] as &$toolCall) {
            if (isset($toolCall['function']['arguments']) && is_string($toolCall['function']['arguments'])) {
                $toolCall['function']['arguments'] = json_decode($toolCall['function']['arguments'], true) ?? [];
            }
        }
        unset($toolCall);

        // assistant message (tool_calls সহ) — একবারই push হবে, loop-এর বাইরে
        $messages[] = $assistantMessage;

        foreach ($assistantMessage['tool_calls'] as $toolCall) {
            $args = $toolCall['function']['arguments'] ?? [];

            $result = $this->toolRegistry->execute(
                $toolCall['function']['name'],
                $args,
                $user,
                $tenantId
            );

            $messages[] = [
                'role' => 'tool',
                'content' => json_encode($result),
            ];
        }
        return $this->chatInternal($messages, $tools, $user, $tenantId, $depth + 1);
    }

    protected function buildSystemPrompt(User $user, ?string $tenantId): string
    {
        if (! $tenantId) {
            return 'তুমি একজন সহায়ক অ্যাসিস্ট্যান্ট।';
        }

        $activeModules = TenantModule::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->with('module:id,name,alias,description,features')
            ->get()
            ->pluck('module')
            ->filter();

        $moduleInfo = $activeModules->map(function ($m) {
            $features = collect(json_decode($m->features ?? '[]', true))
                ->map(fn ($f) => "- {$f}")
                ->implode("\n");

            return "### {$m->name}\n{$m->description}\n\nফিচারসমূহ:\n{$features}";
        })->implode("\n\n");

        return <<<PROMPT
তুমি একজন সহায়ক অ্যাসিস্ট্যান্ট। এই tenant-এর জন্য যেসব মডিউল অ্যাক্টিভ আছে তার তথ্য নিচে দেওয়া হলো — এর বাইরের কোনো মডিউল/ফিচার নিয়ে প্রশ্ন করলে বলবে যে সেটা তাদের প্ল্যানে নেই।

{$moduleInfo}

ইউজার যদি ডেটা-সম্পর্কিত প্রশ্ন করে (যেমন সংখ্যা, তালিকা, পরিসংখ্যান), তাহলে available tools ব্যবহার করে সঠিক ডেটা এনে উত্তর দেবে, অনুমান করবে না।
PROMPT;
    }
}
