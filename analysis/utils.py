# -*- encoding: utf-8 -*-

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
