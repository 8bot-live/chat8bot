# WordPress AI Chatbot Plugin

A free and simple AI chatbot plugin for WordPress that integrates with OpenAI's API to provide intelligent responses to user queries. Unlike cloud-based solutions, all conversations are managed locally on your site, ensuring better control and privacy. You only need an OpenAI API key to get started. The API is a free trial, although if paid, its about 1 cent per chat session depending what model you choose.

## Features

- **Local Conversation Management** – No third-party storage; all chat history is handled within your WordPress installation.
- **OpenAI API Integration** – Simply register for an OpenAI API key and start using the chatbot.
- **Customizable Appearance** – Modify colors, text, and intro messages to match your website branding.
- **Email Notifications** – Set up email alerts for new chat sessions and customer inquiries.
- **Knowledge Base Support** – Enhance AI responses by providing custom knowledge base content tailored to your website.
- **Google reCAPTCHA v2** – Protect against spam and bots.

## Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress admin panel under **Plugins**.
3. Navigate to **Chatbot Settings** in the WordPress dashboard.
4. Enter your OpenAI API key.
5. Configure theme colors, chatbot title, intro message, and email notifications.
6. (Optional) Add a knowledge base for better AI-guided responses.

## Configuration

### OpenAI API Settings
- **API Key** – Register at [OpenAI](https://platform.openai.com/) and enter your API key.
- **Model Selection** – Choose between `gpt-3.5-turbo` and `gpt-4-turbo` depending on your requirements.
- **Max Tokens** – Control response length (default: 100 tokens).
- **Temperature** – Adjust response randomness (default: 1.0 for balanced responses).

### Knowledge Base Support
One of the standout features of this chatbot is its ability to utilize a custom knowledge base. You can define specific information that helps guide how OpenAI responds to visitors on your site. This allows for more tailored and relevant answers based on your business, products, or niche.

To use this feature:
1. Navigate to the **Chatbot Settings** page in WordPress.
2. Enter relevant knowledge base content in the designated section.
3. Save your settings, and the AI will use this information to refine responses.

### Email Notifications
- Receive chat transcripts via email.
- Set email timeout to send notifications if no response is received within a defined period.

### reCAPTCHA v2 Protection
- Enable reCAPTCHA to prevent spam and bot interactions.

## Usage

- After configuration, the chatbot will appear on your website.
- Users can interact with the chatbot, and their queries will be processed using OpenAI’s API.
- Admins can review past conversations locally without third-party data storage.

## Contributions

Feel free to contribute via [GitHub](https://github.com/your-repo-link-here). Pull requests are welcome!

## License

This plugin is open-source and distributed under the MIT License.

---

For questions or support, contact: info@8bot.live

Donate via [PayPal](https://paypal.me/8botlive)