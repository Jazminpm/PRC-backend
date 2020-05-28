# -*- encoding: utf-8 -*-
from googletrans import Translator
from textblob import TextBlob
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer
import requests


def translate(msg, dest='en'):
    params = {'sl': 'es', 'tl': dest, 'q': msg}
    header = {"Charset": "UTF-8",
                 "User-Agent": "AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1"}
    url = "https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e"
    response = requests.post(url, data=params, headers=header)
    if response.status_code == 200:
        for x in response.json()['sentences']:
            return x['trans']
    else:
        return "Ocurrió un error"

def translate(msg, dest='en'):
    return TextBlob(msg).translate(from_lang='es', to='en')


def textblob_comment(msg):
    return TextBlob(msg).sentiment


def textblob_analysis(msg):
    return TextBlob(translate(msg)).sentiment


def vader_analysis(msg):
    return SentimentIntensityAnalyzer().polarity_scores(translate(msg))['compound'], None
