<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "Can HARelay access my Home Assistant?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "HARelay acts as a relay, forwarding traffic between your devices and your Home Assistant. While traffic passes through our servers, we don't log, store, or analyze it. We never see your HA login credentials (those go directly to your HA). We have no way to control your devices or access your automations."
            }
        },
        {
            "@@type": "Question",
            "name": "What happens if HARelay gets hacked?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "In that unlikely scenario: We don't store traffic logs, so there's no historical data to steal. Your HA login credentials are never stored by HARelay - they go directly to your Home Assistant. Your home network has no open ports, so there's no direct path in. You can immediately disconnect by stopping the app (formerly add-on)."
            }
        },
        {
            "@@type": "Question",
            "name": "Is this more secure than port forwarding?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Yes, significantly. Port forwarding exposes your home network directly to the internet, making it visible to attackers and vulnerable to exploits. With HARelay, your network stays completely closed. The only connection is outbound, initiated by you."
            }
        },
        {
            "@@type": "Question",
            "name": "What if I want to disconnect immediately?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Simply stop the HARelay app in Home Assistant. The tunnel closes instantly and no one can access your HA through HARelay until you start it again. You can also regenerate your connection token to invalidate any existing credentials."
            }
        },
        {
            "@@type": "Question",
            "name": "Where are your servers located?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "All our servers are located in Germany and subject to strict EU data protection laws (GDPR). Your data never leaves the European Union, ensuring the highest standards of privacy protection."
            }
        }
    ]
}
</script>
