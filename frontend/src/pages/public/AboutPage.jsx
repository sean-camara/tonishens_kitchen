import { useState, useEffect } from 'react';
import { getAbout } from '../../api/about';
import { ChevronDownIcon } from '@heroicons/react/24/outline';
import Spinner from '../../components/ui/Spinner';

export default function AboutPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [openFaq, setOpenFaq] = useState(null);

  useEffect(() => {
    getAbout().then((res) => setData(res.data.data || res.data)).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="flex justify-center py-20"><Spinner size="lg" /></div>;

  return (
    <div className="container-app py-8 sm:py-12">
      {/* Hero */}
      <div className="text-center mb-12">
        <h1 className="font-heading text-3xl font-bold text-stone-900 sm:text-4xl">About Us</h1>
        <p className="mt-2 text-stone-500 max-w-2xl mx-auto">The story behind Tonishen's Kitchen</p>
      </div>

      {/* Our Story */}
      {data?.history && (
        <section className="mb-12 max-w-3xl mx-auto">
          <h2 className="font-heading text-2xl font-bold text-stone-900 mb-4">Our Story</h2>
          <div className="prose prose-stone max-w-none text-stone-600 leading-relaxed whitespace-pre-line">
            {data.history}
          </div>
        </section>
      )}

      {/* Contact Info */}
      {data?.contacts?.length > 0 && (
        <section className="mb-12">
          <h2 className="font-heading text-2xl font-bold text-stone-900 mb-6 text-center">Contact Us</h2>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 max-w-3xl mx-auto">
            {data.contacts.map((c) => (
              <div key={c.id} className="rounded-xl border border-stone-200 bg-white p-5 text-center shadow-sm">
                <p className="text-xs font-medium uppercase tracking-wider text-primary-600 mb-1">{c.type}</p>
                <p className="text-sm text-stone-700 font-medium">{c.value}</p>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* Social Media */}
      {data?.social_links?.length > 0 && (
        <section className="mb-12 text-center">
          <h2 className="font-heading text-2xl font-bold text-stone-900 mb-6">Follow Us</h2>
          <div className="flex flex-wrap justify-center gap-3">
            {data.social_links.map((s) => (
              <a
                key={s.id}
                href={s.url}
                target="_blank"
                rel="noopener noreferrer"
                className="rounded-lg border border-stone-200 bg-white px-5 py-2.5 text-sm font-medium text-stone-700 shadow-sm transition-all hover:border-primary-300 hover:text-primary-600 hover:shadow-md"
              >
                {s.platform}
              </a>
            ))}
          </div>
        </section>
      )}

      {/* FAQs */}
      {data?.faqs?.length > 0 && (
        <section className="max-w-2xl mx-auto">
          <h2 className="font-heading text-2xl font-bold text-stone-900 mb-6 text-center">Frequently Asked Questions</h2>
          <div className="space-y-2">
            {data.faqs.map((faq) => (
              <div key={faq.id} className="rounded-xl border border-stone-200 bg-white overflow-hidden">
                <button
                  onClick={() => setOpenFaq(openFaq === faq.id ? null : faq.id)}
                  className="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-medium text-stone-800 hover:bg-stone-50 transition-colors"
                >
                  {faq.question}
                  <ChevronDownIcon className={`h-4 w-4 shrink-0 text-stone-400 transition-transform ${openFaq === faq.id ? 'rotate-180' : ''}`} />
                </button>
                {openFaq === faq.id && (
                  <div className="border-t border-stone-100 px-5 py-4 text-sm text-stone-600 leading-relaxed">
                    {faq.answer}
                  </div>
                )}
              </div>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
