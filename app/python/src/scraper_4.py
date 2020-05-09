import json
import sys

from datetime import datetime
from scrapers.utils import get_tweets_by_hashtag

if __name__ == "__main__":
    today = datetime.now().strftime("%Y-%m-%d")
    get_tweets_by_hashtag(hashtag='Madrid', date=today, lang='es')
