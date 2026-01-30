<x-marketing-layout>
    <x-slot name="title">Privacy Policy - HARelay</x-slot>
    <x-slot name="description">Learn how HARelay handles your data and protects your privacy.</x-slot>

    <div class="py-16 sm:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-white mb-8">Privacy Policy</h1>
            <p class="text-slate-400 mb-8">Last updated: {{ date('F j, Y') }}</p>

            <div class="prose prose-invert prose-slate max-w-none">
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-8">
                    <h2 class="text-2xl font-semibold text-white mb-4">Our Commitment to Privacy</h2>
                    <p class="text-slate-300">
                        HARelay is built with privacy as a core principle. We collect only the minimum data necessary to provide our service, and we never sell or share your personal information with third parties for marketing purposes.
                    </p>
                </div>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">1. Information We Collect</h2>

                    <h3 class="text-xl font-medium text-white mt-6 mb-3">Account Information</h3>
                    <p class="text-slate-300 mb-4">
                        When you create an account, we collect:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li>Your name (for account identification)</li>
                        <li>Your email address (for account access and important notifications)</li>
                        <li>Password (stored securely using bcrypt hashing)</li>
                    </ul>

                    <h3 class="text-xl font-medium text-white mt-6 mb-3">Connection Data</h3>
                    <p class="text-slate-300 mb-4">
                        To provide the tunnel service, we store:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li>Your unique subdomain</li>
                        <li>Connection tokens (hashed, never stored in plain text)</li>
                        <li>Connection status and last connection timestamp</li>
                    </ul>

                    <h3 class="text-xl font-medium text-white mt-6 mb-3">Traffic Data</h3>
                    <p class="text-slate-300 mb-4">
                        <strong class="text-white">Important:</strong> We do NOT store, log, or inspect the content of your Home Assistant traffic. The tunnel simply forwards encrypted data between your devices and your Home Assistant instance. We cannot see your dashboards, automation data, or any other Home Assistant content.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">2. How We Use Your Information</h2>
                    <p class="text-slate-300 mb-4">
                        We use your information solely to:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li>Provide and maintain the HARelay service</li>
                        <li>Authenticate your connections</li>
                        <li>Send important service notifications (security alerts, maintenance notices)</li>
                        <li>Respond to your support requests</li>
                    </ul>
                    <p class="text-slate-300 mt-4">
                        We do NOT use your data for advertising, profiling, or any purpose other than providing the tunnel service.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">3. Data Security</h2>
                    <p class="text-slate-300 mb-4">
                        We implement industry-standard security measures:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li>All connections are encrypted using TLS (HTTPS/WSS)</li>
                        <li>Passwords are hashed using bcrypt with strong salting</li>
                        <li>Connection tokens are hashed and never stored in plain text</li>
                        <li>Database access is restricted and monitored</li>
                        <li>Regular security updates and maintenance</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">4. Data Retention</h2>
                    <p class="text-slate-300 mb-4">
                        We retain your account data as long as your account is active. If you delete your account:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li>Your account information is permanently deleted</li>
                        <li>Your connection data and subdomain are released</li>
                        <li>This process is immediate and irreversible</li>
                    </ul>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">5. Third-Party Services</h2>
                    <p class="text-slate-300 mb-4">
                        HARelay uses minimal third-party services:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li><strong class="text-white">Hosting Provider:</strong> Our servers are hosted on secure infrastructure with appropriate data protection agreements in place.</li>
                        <li><strong class="text-white">Email Service:</strong> We may use email providers to send transactional emails (account verification, password reset).</li>
                    </ul>
                    <p class="text-slate-300 mt-4">
                        We do not integrate any advertising networks, analytics trackers, or social media pixels.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">6. Your Rights</h2>
                    <p class="text-slate-300 mb-4">
                        You have the right to:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li><strong class="text-white">Access:</strong> View the personal data we hold about you</li>
                        <li><strong class="text-white">Correction:</strong> Update your account information at any time</li>
                        <li><strong class="text-white">Deletion:</strong> Delete your account and all associated data</li>
                        <li><strong class="text-white">Export:</strong> Request a copy of your data</li>
                    </ul>
                    <p class="text-slate-300 mt-4">
                        To exercise these rights, you can use the settings in your dashboard or contact us directly.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">7. Cookies</h2>
                    <p class="text-slate-300 mb-4">
                        We use only essential cookies required for the service to function:
                    </p>
                    <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                        <li><strong class="text-white">Session Cookie:</strong> Keeps you logged in during your session</li>
                        <li><strong class="text-white">CSRF Token:</strong> Protects against cross-site request forgery attacks</li>
                    </ul>
                    <p class="text-slate-300 mt-4">
                        We do not use tracking cookies, advertising cookies, or any non-essential cookies.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">8. Children's Privacy</h2>
                    <p class="text-slate-300">
                        HARelay is not intended for use by children under 16. We do not knowingly collect personal information from children. If you believe a child has provided us with personal information, please contact us.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">9. Changes to This Policy</h2>
                    <p class="text-slate-300">
                        We may update this privacy policy from time to time. We will notify you of any significant changes by email or through a notice on our website. Your continued use of the service after changes constitutes acceptance of the updated policy.
                    </p>
                </section>

                <section class="mb-12">
                    <h2 class="text-2xl font-semibold text-white mb-4">10. Contact Us</h2>
                    <p class="text-slate-300">
                        If you have questions about this privacy policy or our data practices, please contact us through the information provided in our imprint.
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-marketing-layout>
