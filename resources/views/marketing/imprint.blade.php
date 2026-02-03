<x-marketing-layout>
    <x-slot name="title">Imprint - HARelay</x-slot>
    <x-slot name="description">Legal information and contact details for HARelay.</x-slot>

    <x-slot name="structuredData">
        <script type="application/ld+json">
        {
t            "@@context": "https://schema.org",
            "@@type": "WebPage",
            "name": "Imprint - HARelay",
            "description": "Legal information and contact details for HARelay.",
            "url": "{{ url()->current() }}",
            "mainEntity": {
                "@@type": "Organization",
                "name": "HARelay",
                "url": "https://harelay.com",
                "email": "mathias@harelay.com",
                "founder": {
                    "@@type": "Person",
                    "name": "Mathias Placho"
                },
                "address": {
                    "@@type": "PostalAddress",
                    "streetAddress": "Frauengasse 7",
                    "addressLocality": "Graz",
                    "postalCode": "8010",
                    "addressCountry": "AT"
                }
            }
        }
        </script>
    </x-slot>

    <div class="py-16 sm:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-white mb-8">Imprint</h1>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Service Provider</h2>
                <div class="text-slate-300 space-y-2">
                    <p>HARelay</p>
                    <p>Mathias Placho</p>
                    <p>Frauengasse 7</p>
                    <p>8010 Graz</p>
                    <p>Österreich</p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Contact</h2>
                <div class="text-slate-300 space-y-2">
                    <p>Email: <a href="mailto:mathias@harelay.com" class="text-cyan-400 hover:text-cyan-300 transition">mathias@harelay.com</a></p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Responsible for Content</h2>
                <div class="text-slate-300 space-y-2">
                    <p class="mt-2">Mathias Placho</p>
                    <p>Frauengasse 7</p>
                    <p>8010 Graz</p>
                    <p>Österreich</p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Dispute Resolution</h2>
                <div class="text-slate-300 space-y-4">
                    <p>
                        The European Commission provides a platform for online dispute resolution (ODR):
                        <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300 transition">https://ec.europa.eu/consumers/odr/</a>
                    </p>
                    <p>
                        We are neither obligated nor willing to participate in dispute resolution proceedings before a consumer arbitration board.
                    </p>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Liability for Content</h2>
                <div class="text-slate-300 space-y-4">
                    <p>
                        As a service provider, we are responsible for our own content on these pages according to general laws (§ 7 Abs.1 TMG). However, according to § 8 to 10 TMG, we are not obligated to monitor transmitted or stored third-party information or to investigate circumstances that indicate illegal activity.
                    </p>
                    <p>
                        Obligations to remove or block the use of information under general laws remain unaffected. However, liability in this regard is only possible from the time of knowledge of a specific infringement. Upon becoming aware of corresponding infringements, we will remove this content immediately.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-marketing-layout>
