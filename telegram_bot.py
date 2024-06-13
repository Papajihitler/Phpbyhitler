import logging
import requests
from telegram import Update
from telegram.ext import Application, CommandHandler, CallbackContext

# Set up logging
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# Function to check proxy status
def check_proxy(proxy):
    ip, port, username, password = proxy.split(':')
    proxy_url = f'http://{username}:{password}@{ip}:{port}'
    proxies = {
        "http": proxy_url,
        "https": proxy_url
    }

    try:
        response = requests.get('http://www.google.com', proxies=proxies, timeout=5)
        if response.status_code == 200:
            return True
    except requests.RequestException:
        pass
    return False

# Command handler for /check
async def check(update: Update, context: CallbackContext) -> None:
    if len(context.args) != 1:
        await update.message.reply_text("Usage: /check <ip:port:username:password>")
        return

    proxy = context.args[0]

    if check_proxy(proxy):
        status_message = f"⊙ Status: Live ✅\n⊙ Proxy: {proxy}\n\nDev ~ @HitlerxPapaa⚡️"
    else:
        status_message = f"⊙ Status: Dead ❌\n⊙ Proxy: {proxy}\n\nDev ~ @HitlerxPapaa"

    await update.message.reply_text(status_message)

def main():
    # Replace 'YOUR_TOKEN' with your actual bot token
    application = Application.builder().token("7122838146:AAFSbDgukcfPTgMWL_pZ5TQ0DtJTRiv_suQ").build()

    # Add the /start command handler
    application.add_handler(CommandHandler("check", check))

    # Run the bot
    application.run_polling()

if __name__ == '__main__':
    main()
