<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "HowTo",
    "name": "How to Set Up HARelay for Home Assistant Remote Access",
    "description": "Learn how HARelay provides secure remote access to your Home Assistant in three simple steps.",
    "totalTime": "PT5M",
    "step": [
        {
            "@@type": "HowToStep",
            "position": 1,
            "name": "Create Your Account",
            "text": "Sign up for a free HARelay account. You'll get a unique subdomain automatically assigned to you.",
            "url": "{{ route('register') }}"
        },
        {
            "@@type": "HowToStep",
            "position": 2,
            "name": "Install the Home Assistant Add-on",
            "text": "Add the HARelay repository (https://github.com/harelay/ha-addon) to Home Assistant's Add-on Store and install the HARelay add-on. Start the add-on and it will automatically enter pairing mode."
        },
        {
            "@@type": "HowToStep",
            "position": 3,
            "name": "Enter the Pairing Code",
            "text": "The add-on displays a simple pairing code. Copy the code and enter it on HARelay. Your Home Assistant is now connected and accessible from anywhere!"
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        {
            "@@type": "Question",
            "name": "Is HARelay really free?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Yes! HARelay is completely free to use. We built this for the Home Assistant community and want everyone to have secure remote access without barriers."
            }
        },
        {
            "@@type": "Question",
            "name": "Do I need to open any ports on my router?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "No. HARelay uses outbound WebSocket connections, so your Home Assistant stays behind your firewall with no ports exposed. This is actually more secure than traditional port forwarding."
            }
        },
        {
            "@@type": "Question",
            "name": "Is my data secure?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "Absolutely. All connections are encrypted using TLS, and we never store your Home Assistant credentials. The tunnel only forwards traffic - we can't see your data."
            }
        },
        {
            "@@type": "Question",
            "name": "What about performance?",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "HARelay uses WebSocket connections for low latency. Most users don't notice any difference compared to a direct local connection. Real-time features like live dashboards work seamlessly."
            }
        }
    ]
}
</script>
