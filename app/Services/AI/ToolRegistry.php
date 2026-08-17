<?php
// app/Services/AI/ToolRegistry.php
namespace App\Services\AI;

use App\Enums\UserType;
use App\Models\TenantModule;
use App\Models\User;
use App\Services\AI\Contracts\AdminToolProvider;

class ToolRegistry
{
    protected array $moduleProviders = [];
    protected ?AdminToolProvider $adminProvider = null;

    public function register(Contracts\AiToolProvider $provider): void
    {
        $this->moduleProviders[$provider->moduleAlias()] = $provider;
    }

    public function registerAdmin(AdminToolProvider $provider): void
    {
        $this->adminProvider = $provider;
    }

    public function toolsForUser(User $user, ?string $tenantId = null): array
    {
       
        if ($user->user_type === UserType::SUPER_ADMIN) {
            return $this->adminProvider?->tools() ?? [];
        }
        if ($tenantId) {
            return $this->toolsForTenant($tenantId);
        }

        return [];
    }

    public function toolsForTenant(string $tenantId): array
    {
        $activeAliases = TenantModule::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->with('module:id,alias')
            ->get()
            ->pluck('module.alias');

        $tools = [];
        foreach ($this->moduleProviders as $alias => $provider) {
            if ($activeAliases->contains($alias)) {
                $tools = array_merge($tools, $provider->tools());
            }
        }

        return $tools;
    }

    public function execute(string $toolName, array $args, User $user, ?string $tenantId = null): mixed
    {
      
        if ($this->isAdminTool($toolName)) {
            if ($user->user_type !== UserType::SUPER_ADMIN) {
                return ['error' => 'Unauthorized: এই তথ্য শুধু admin দেখতে পারবে'];
            }
            return $this->adminProvider->execute($toolName, $args);
        }

        foreach ($this->moduleProviders as $provider) {
            $names = array_column(array_column($provider->tools(), 'function'), 'name');
            if (in_array($toolName, $names)) {
                return $provider->execute($toolName, $args, $tenantId);
            }
        }

        return ['error' => 'Tool not found'];
    }

    protected function isAdminTool(string $toolName): bool
    {
        $adminToolNames = array_column(array_column($this->adminProvider?->tools() ?? [], 'function'), 'name');
        return in_array($toolName, $adminToolNames);
    }
}
