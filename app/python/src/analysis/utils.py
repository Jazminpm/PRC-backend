# -*- encoding: utf-8 -*-

from googletrans import Translator
from textblob import TextBlob
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer


def translate(msg, dest='en'):
    return Translator().translate(msg, dest=dest).text


def textblob_comment(msg):
    return TextBlob(msg).sentiment


def textblob_analysis(msg):
    return TextBlob(translate(msg)).sentiment


def vader_analysis(msg):
    return SentimentIntensityAnalyzer().polarity_scores(translate(msg))['compound'], None
