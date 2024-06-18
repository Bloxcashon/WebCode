import os
import requests

# Load environment variables from .env file
from dotenv import load_dotenv
load_dotenv()

# Get the token from the .env file
token = os.getenv('TOKEN')

# Set the headers with the token
headers = {
    'Cookie': f'.ROBLOSECURITY={token}',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
}

# Purchase the game pass using its URL
game_pass_url = 'https://www.roblox.com/game-pass/828002640/oneee'
response = requests.post(game_pass_url, headers=headers)

# Print the response status code
print(f'Response status code: {response.status_code}')

# Check if the purchase was successful
if response.status_code == 200:
    print('Game pass purchased successfully!')
else:
    print('Failed to purchase game pass. Check the game pass URL or your token.')
    print(f'Response reason: {response.reason}')
    print(f'Response text: {response.text}')