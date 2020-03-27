# -*- encoding: utf-8 -*-

import json
import re
from datetime import datetime, date

import tweepy

import requests
from bs4 import BeautifulSoup
import numpy as np

consumer_key = "F2pIrutjymGr9vZuqTeViAymw"
consumer_secret = "QFLTXwFJZNuR6i00IswAZIgaKsKl5AtmPufoSaRnR57ER2yVxS"
access_token = "879408865997201408-QJDeVNKYBsTdp97caK0qFV454YYwRLp"
access_token_secret = "gLNfeVRjT7LrDaWWSgCX0ZRu6TeTPBquTxlYKQ4hUzAka"

auth = tweepy.OAuthHandler(consumer_key, consumer_secret)
auth.set_access_token(access_token, access_token_secret)
api = tweepy.API(auth, wait_on_rate_limit=True)


def remove_emoji(msg):
    """Remove emojis characters from an input string.

    Note:
        Code obtained from https://stackoverflow.com/questions/33404752/removing-emojis-from-a-string-in-python

    Args:
        msg (str): Message to remove emojis.

    Returns:
        Same msg (str) with removed emojis.
    """
    emoji_pattern = re.compile("["
                               u"\U0001F600-\U0001F64F"  # emoticons
                               u"\U0001F300-\U0001F5FF"  # symbols & pictographs
                               u"\U0001F680-\U0001F6FF"  # transport & map symbols
                               u"\U0001F1E0-\U0001F1FF"  # flags (iOS)
                               u"\U00002702-\U000027B0"
                               u"\U000024C2-\U0001F251"
                               "]+", flags=re.UNICODE)
    return emoji_pattern.sub(r'', msg)


def get_tweets_by_hashtag(hashtag, date, lang='en'):
    """Get tweets from a day with a hashtag filter.

    Notes:
        The hashtag parameter is for search a city.

    Args:
        hashtag (str): Some hashtag to search in twitter.
        date (str): Date in YYYY-mm-dd format.
        lang (str): Restricts tweets to the given language, given by an ISO 639-1 code.
    """
    query = '#' + hashtag + ' AND #travel OR #viaje -filter:retweets'
    for tweet in tweepy.Cursor(api.search, q=query, since=date, lang=lang, tweet_mode='extended').items():
        ee = {
            'date': tweet.created_at.strftime("%Y-%m-%d"),
            'comment': remove_emoji(tweet.full_text)
        }
        print(json.dumps(ee, ensure_ascii=False))


def select_date_tu_tiempo(day, month, year):
    actual_day = date.today().day
    actual_month = date.today().month
    actual_year = date.today().year

    days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
    if year % 4 == 0:  # año bisiesto
        days_in_month[1] = 29

    while day != actual_day or month != actual_month - 1 or year != actual_year:
        tu_tiempo(str(day), month, str(year))
        if day != days_in_month[month]:
            day = day + 1
        elif month != 11:
            month = month + 1
            day = 1
        else:
            month = 0
            day = 1
            year = year + 1


def tu_tiempo(day, month, year):
    months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre',
              'noviembre', 'diciembre']
    wind_directions = np.array(
        ['En calma', 'Norte', 'Nordeste', 'Este', 'Sureste', 'Sur', 'Suroeste', 'Oeste', 'Noroeste', 'Variable'])

    page = requests.get('https://www.tutiempo.net/registros/lemd/' + day + '-' + months[month] + '-' +
                        year + '.html')

    soup = BeautifulSoup(page.content, 'html.parser')
    tr = soup.find('div', class_='last24 thh mt10').findAll('tr')

    for i in range(len(tr)):
        if 1 < i < len(tr) - 2 and i % 2 == 0:
            td = tr[i].findAll('td')
            wind_direction = np.where(wind_directions == [td[3].find('img').get('title')])[0]

            speed = 0
            if td[3].getText() != 'En calma':
                speed = re.findall(r"[\d]+", td[3].getText())[0]

            response = {
                'hour': td[0].getText(),
                'temperature': re.findall(r"[-]*[\d]+", td[2].getText())[0],
                'wind_speed': speed,
                'wind_direction': str(wind_direction[0]),
                'humidity': re.findall(r"[\d]+", td[4].getText())[0],
                'pressure': re.findall(r"[\d]+", td[5].getText())[0]
            }
            print(response)


if __name__ == "__main__":
    today = datetime.now().strftime("%Y-%m-%d")
    get_tweets_by_hashtag(hashtag='Madrid', date=today, lang='es')
