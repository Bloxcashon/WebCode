import requests
import time
import os
import sys 

print("Python script started with gamepass ID:", sys.argv[1])

class RobloxBot:
    def __init__(self, cookie: str):
        self.cookie = cookie

    def get_user_id(self):
        url = "https://users.roblox.com/v1/users/authenticated"
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"}
        cookies = {".ROBLOSECURITY": self.cookie}
        req = requests.get(url, headers=headers, cookies=cookies)
        return req.json()['id']

    def get_username(self):
        url = "https://users.roblox.com/v1/users/authenticated"
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"}
        cookies = {".ROBLOSECURITY": self.cookie}
        req = requests.get(url, headers=headers, cookies=cookies)
        return req.json()['name']

    def get_game_pass_info(self, game_pass_id: int):
        url = f"https://apis.roblox.com/game-passes/v1/game-passes/{game_pass_id}/product-info"
        req = requests.get(url)
        return req.json()

    def get_xsrf_token(self):
        url = "https://auth.roblox.com/v2/logout"
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"}
        cookies = {".ROBLOSECURITY": self.cookie}
        req = requests.post(url, headers=headers, cookies=cookies)
        return req.headers['x-csrf-token']

    def buy_game_pass(self, game_pass_id: int):
        username = self.get_username()
        print(f"Logged in successfully using {username}")
        game_pass_info = self.get_game_pass_info(game_pass_id)
        print(f"Game pass ID: {game_pass_id}, Name: {game_pass_info['Name']}, Price: {game_pass_info['PriceInRobux']} Robux")
        user_id = self.get_user_id()
        data = {"expectedCurrency": 1, "expectedPrice": game_pass_info['PriceInRobux'], "expectedSellerId": game_pass_info['Creator']['Id']}
        url = f"https://economy.roblox.com/v1/purchases/products/{game_pass_info['ProductId']}"
        headers = {"X-CSRF-TOKEN": self.get_xsrf_token()}
        cookies = {".ROBLOSECURITY": self.cookie}
        req = requests.post(url, data=data, headers=headers, cookies=cookies)
        if req.status_code == 200:
            print(f"Game pass {game_pass_id} purchased successfully!")
        else:
            print(f"Error purchasing game pass {game_pass_id}: {req.text}")

    def get_robux_amount(self):
        url = f"https://economy.roblox.com/v1/users/{self.get_user_id()}/currency"
        headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Safari/537.36"}
        cookies = {".ROBLOSECURITY": self.cookie}
        req = requests.get(url, headers=headers, cookies=cookies)
        return req.json()["robux"]

    def auto_buy_game_passes(self, game_pass_ids_str: str, amount: int, cooldown_time: int):
        game_pass_ids = [int(x) for x in game_pass_ids_str.split(',')]
        for i in range(amount):
            for game_pass_id in game_pass_ids:
                robux_amount = self.get_robux_amount()
                game_pass_info = self.get_game_pass_info(game_pass_id)
                if robux_amount >= game_pass_info['PriceInRobux']:
                    self.buy_game_pass(game_pass_id)
                else:
                    print(f"Not enough Robux to purchase game pass {game_pass_id}. Need {game_pass_info['PriceInRobux']} Robux.")
                time.sleep(cooldown_time)

# Example usage:
if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Error: Gamepass ID is required as an argument.")
        exit(1)

    gamepass_id = int(sys.argv[1])
    cookie = os.getenv("ROBLOX_COOKIE")
    if cookie is None:
        print("Error: ROBLOX_COOKIE environment variable not set.")
        exit(1)

    bot = RobloxBot(cookie)
    bot.buy_game_pass(gamepass_id)