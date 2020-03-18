# -*- encoding: utf-8 -*-

import json
import sys

from googletrans import Translator
from textblob import TextBlob
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer


def translate(msg, dest='en'):
    return Translator().translate(msg, dest=dest).text


def textblob_analysis(msg):
    return TextBlob(translate(msg)).sentiment


def vader_analysis(msg):
    sentiment = SentimentIntensityAnalyzer().polarity_scores(translate(msg))
    return sentiment['compound'], None


if __name__ == "__main__":
    data = json.loads(sys.argv[1])

    if data['lib'] is 1:
        sentiment = textblob_analysis(data['msg'])
    elif data['lib'] is 2:
        sentiment = vader_analysis(data['msg'])
    response = {
        'polarity': sentiment[0],
        'subjectivity': sentiment[1]
    }

    print(json.dumps(response))
