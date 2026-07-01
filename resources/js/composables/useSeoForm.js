  // resources/js/Composables/useSeoForm.js
  import { useForm } from '@inertiajs/vue3';

  export function useSeoForm(seo = {}) {
      const form = useForm({
          meta_title: seo?.meta_title ?? '',
          meta_description: seo?.meta_description ?? '',
          meta_keywords: seo?.meta_keywords ?? '',
          canonical_url: seo?.canonical_url ?? '',
          robots: seo?.robots ?? 'index,follow',
          og_title: seo?.og_title ?? '',
          og_description: seo?.og_description ?? '',
          og_image: seo?.og_image ?? null,
          og_type: seo?.og_type ?? 'website',
          twitter_title: seo?.twitter_title ?? '',
          twitter_description: seo?.twitter_description ?? '',
          twitter_card: seo?.twitter_card ?? 'summary_large_image',
          schema_type: seo?.schema_type ?? '',
          focus_keyword: seo?.focus_keyword ?? '',
          sitemap_include: seo?.sitemap_include ?? true,
          sitemap_priority: seo?.sitemap_priority ?? 0.5,
          sitemap_frequency: seo?.sitemap_frequency ?? 'weekly',
      });

      function submitSeo(routeName, routeParam) {
          form.transform((data) => ({
              ...data,
              og_image: data.og_image instanceof File ? data.og_image : undefined,
              _method: 'put',
          })).post(route(routeName, routeParam), {
              forceFormData: true,
              preserveScroll: true,
          });
      }
      function submitUrlSeo(url, id) {
          form.transform((data) => ({
              ...data,
              og_image: data.og_image instanceof File ? data.og_image : undefined,
              _method: 'put',
          })).post(`${url}`, {
              forceFormData: true,
              preserveScroll: true,
          });
      }

      return { form, submitSeo , submitUrlSeo };
  }
