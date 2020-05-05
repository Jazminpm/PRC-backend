# -*- encoding: utf-8 -*-

import asyncio
import json
import re
from datetime import datetime, date
from datetime import timedelta

import numpy as np
import requests
import tweepy
from bs4 import BeautifulSoup
from pyppeteer import launch
from unidecode import unidecode

import time


API = "http://127.0.0.1:8000/api/"
ENDPOINT = {
    'weather': API + 'weather'
}


URL_TUTIEMPO = "https://www.tutiempo.net/registros/"
URL_ELTIEMPO = "https://www.eltiempo.es/barajas.html?v=por_hora"
URL_WEATERFORYOU = "https://www.weatherforyou.com/reports/index.php?config=&forecast=pass&pass=hourly_metric&pands" \
                   "=&zipcode=&icao="
LEMD_ID = 5327
LEMD = "lemd"



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


def tu_tiempo(str_date, airport_id, icao):
    """Get weather data from URL_TUTIEMPO and post in an endpoint.

    Args:
        str_date (str): Date in YYYY-mm-dd format.
        airport_id (int): Airport ID code.
        icao (str): Airport ICAO code.
    """
    months = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']
    wind_directions = np.array(['', 'En calma', 'Norte', 'Nordeste', 'Este', 'Sureste', 'Sur', 'Suroeste', 'Oeste', 'Noroeste', 'Variable'])

    str_date = datetime.strptime(str_date, '%Y-%m-%d')

    day = int(str_date.strftime('%d'))
    month = months[int(str_date.strftime('%m'))]
    year = str_date.strftime('%Y')

    url_tutiempo_airport = URL_TUTIEMPO + icao + '/' + str(day) + '-' + month + '-' + year + '.html'

    page = requests.get(url_tutiempo_airport)
    soup = BeautifulSoup(page.content, 'html.parser')
    tr = soup.find('div', class_='last24 thh mt10').find_all('tr')

    for i in range(len(tr)):
        if 1 < i < len(tr) - 2 and i % 2 == 0:
            td = tr[i].findAll('td')
            if len(td) > 0:
                wind_direction = np.where(wind_directions == [td[3].find('img').get('title')])[0]

                speed = 0
                if td[3].getText() != 'En calma':
                    speed = re.findall(r"[\d]+", td[3].getText())[0]

                dt = str_date.strftime('%Y-%m-%d') + ' ' + td[0].getText() + ':00'

                if len(re.findall(r"[\d]+", td[5].getText())) > 0:
                    pressure = int(re.findall(r"[\d]+", td[5].getText())[0])
                else:
                    pressure = 1000

                weather_json = {
                    'date_time': dt,
                    'temperature': int(re.findall(r"[-]*[\d]+", td[2].getText())[0]),
                    'wind_speed': int(speed),
                    'wind_direction': int(wind_direction[0]),
                    'humidity': int(re.findall(r"[\d]+", td[4].getText())[0]),
                    'pressure': pressure,
                    'airport_id': airport_id  # TODO: implements all airports
                }
                print(json.dumps(weather_json))


def wind_direction_id(wind_direction):
    directions = ["", "calm", "north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west", "variable"]
    return directions.index(wind_direction)


def el_tiempo():
    """Get weather data from URL_ELTIEMPO and post in an endpoint.

    """
    tomorrow = (datetime.now() + timedelta(days=1)).strftime('%Y-%m-%d')
    page = requests.get(URL_ELTIEMPO)
    soup = BeautifulSoup(page.content, 'html.parser')

    table = soup.find_all('div', class_='m_table_weather_hour_detail by_hour')[1]


    for row in table.find_all('div', attrs={'data-expand-tablechild-item': True}):

        weather_json = {
            "date_time": tomorrow + ' ' + row.find('div', class_='m_table_weather_hour_detail_hours').getText() + ':00',
            'temperature': int(row.find('div', class_='m_table_weather_hour_detail_pred').getText().split('°')[0]),
            'wind_speed': int(row.find('div', class_='m_table_weather_hour_detail_med').getText().split(' ')[0]),
            'wind_direction': wind_direction_id(row.find('div', class_='m_table_weather_hour_detail_wind').find('i')['class'][-1]),
            'humidity': int(row.find('div', class_='m_table_weather_hour_detail_child m_table_weather_hour_detail_hum').find_all('span')[1].getText().split('%')[0]),
            'pressure': int(row.find('div', class_='m_table_weather_hour_detail_child m_table_weather_hour_detail_preas').find_all('span')[1].getText().split()[0]),
            'airport_id': LEMD_ID  # todo: get more airports
        }
        print(json.dumps(weather_json))
    # r = requests.post(url=ENDPOINT['weather'], data=json.dumps(weather_json))


def weather_for_you(airport_id, icao):
    """Get weather data from URL_WEATERFORYOU and post in an endpoint.

    Args:
        airport_id (int): Airport ID code.
        icao (str): Airport ICAO code.
    """
    wind_directions = np.array(['', '', 'N', 'NNE NE ENE', 'E', 'ESE SE SSE', 'S', 'SSW SW WSW', 'W', 'WNW NW NNW', ''])
    week_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

    url = URL_WEATERFORYOU + icao

    page = requests.get(url)
    soup = BeautifulSoup(page.content, 'html.parser')
    [tag.decompose() for tag in soup("style")]
    [tag.decompose() for tag in soup("script")]

    table = soup.findAll('table')[1]
    divs = table.findAll('div', class_='hourly_cal_colwrap cal_dayiconhi')

    today = date.today()
    week_day = week_days[today.weekday()]

    for div in divs:
        span = div.findAll('span')
        a = div.findAll('a')

        # Get hour in correct format
        hour = span[0].getText().replace('PM', ':00 PM').replace('AM', ':00 AM')
        if span[1].getText() == week_day:
            hour = str(today) + ' ' + hour
            date_time = datetime.strptime(hour, '%Y-%m-%d %I:%M %p')
        else:
            today = today + timedelta(days=1)
            week_day = week_days[today.weekday()]
            hour = str(today) + ' ' + hour
            date_time = datetime.strptime(hour, '%Y-%m-%d %I:%M %p')

        # Get number of wind direction
        wind_regex = re.findall(r"[A-Z]+", a[3].getText())[0]
        direction = [x for x in wind_directions if x == wind_regex]
        if len(direction) == 0:
            direction = [x for x in wind_directions if wind_regex in x]
        wind_direction = np.where(wind_directions == direction[0])[0][0]

        weather_json = {
            'date_time': str(date_time),
            'temperature': int(re.findall(r"[-]*[\d]+", span[2].getText())[0]),
            'wind_speed': re.findall(r"[\d]+", a[4].getText())[0],
            'wind_direction': int(wind_direction),
            'humidity': int(re.findall(r"[\d]+", a[2].getText())[0]),
            'pressure': int(re.findall(r"[\d]+", a[5].getText())[0]),
            'airport_id': airport_id
        }
        print(json.dumps(weather_json))


def select_historical_date(str_date, url, airport_id):
    today = date.today()
    str_date = datetime.strptime(str_date, '%Y-%m-%d')

    day = int(str_date.strftime('%d'))
    month = int(str_date.strftime('%m'))
    year = int(str_date.strftime('%Y'))

    select_date = date(year, month, day)

    if (today - select_date).days > 5:
        select_date = today - timedelta(days=5)
        date_list = [(select_date + timedelta(days=d)).strftime("%Y-%m-%d")
                     for d in range((today - select_date).days)]
    else:
        date_list = [(select_date + timedelta(days=d)).strftime("%Y-%m-%d")
                     for d in range((today - select_date).days)]

    for dat in date_list:
        asyncio.get_event_loop().run_until_complete(select_url(dat[8:10], dat[5:7], dat[0:4], url, airport_id))


def select_future_date(url, airport_id):
    today = str(date.today())
    asyncio.get_event_loop().run_until_complete(select_url(today[8:10], today[5:7], today[0:4], url, airport_id))


async def select_url(day, month, year, url, airport_id):
    browser = await launch(args=['--no-sandbox'])
    page = await browser.newPage()
    await page.goto('https://www.airportia.com' + url + 'departures/')
    await page.waitFor(6000)
    await page.select('.flightsFilter-select--date', year + month + day)
    await page.select('.flightsFilter-select--fromTime', '0000')
    await page.select('.flightsFilter-select--toTime', '2359')

    await page.click('.flightsFilter-submit')

    html = await page.evaluate('new XMLSerializer().serializeToString(document.doctype) + '
                               'document.documentElement.outerHTML')
    scraper_airportia(html, day, month, year, airport_id)
    await browser.close()


def scraper_airportia(html, day, month, year, airport_id):
    soup = BeautifulSoup(html, 'html.parser')
    trs = soup.find('table', class_='flightsTable').findAll('tr')
    i = 0
    for tr in trs:
        if i != 0:
            identifier = tr.find('td', class_='flightsTable-number')
            if identifier is not None:
                td = tr.findAll('td')
                delay = 0
                if td[6].find('div') is not None:
                    if td[6].find('div').getText() == 'Landed':  # En hora
                       delay = 0
                    elif td[6].find('div').getText() == 'Landed Late' or 'Delayed' in td[6].find('div').getText():  # Con retraso
                        delay = 1
                    elif td[6].find('div').getText() == 'Cancelled':  # Cancelada
                        delay = 2
                    elif td[6].find('div').getText() == 'Scheduled':  # Programada
                        delay = 3
                    elif td[6].find('div').getText() == 'Unknown':  # No se conoce el estado
                        delay = 4
                    elif td[6].find('div').getText() == 'Diverted': # Desviado
                        delay = 5
                    elif td[6].find('div').getText() == 'En-Route': # En ruta
                        delay = 6
                    else :  #Nuevo estado desconocido
                        delay = 7


                result = {
                    'id': identifier.find('a').getText(),
                    'date_time': year + '-' + month + '-' + day + ' ' + td[4].getText(),
                    'airline_id': td[2].getText(),
                    'city_id': td[1].find('span').getText(),
                    'airport_id': airport_id,  # todo: get more airports
                    'delay': delay,  # 0->ok, 1->late, 2->cancelled
                }
                print(json.dumps(result))
        i = i + 1

# Get airport names according of the country
async def airports_name(airport_name):
    browser = await launch(args=['--no-sandbox'])
    page = await browser.newPage()

    await page.goto('https://www.airportia.com/' + airport_name + "/")
    await page.waitFor(6000)
    html = await page.evaluate('new XMLSerializer().serializeToString(document.doctype) + '
                               'document.documentElement.outerHTML')
    soup = BeautifulSoup(html, 'html.parser')
    airports = soup.find('div', class_='textlist-body').findAll('a')
    for airport in airports:
        result = {
            'airport_id': re.findall(r"\([A-Za-z]+\)", airport.getText())[0].replace('(', '').replace(')', ''),
            'airport_url': airport.get('href'),
        }
        print(json.dumps(result))
    await browser.close()

# Get trip advisor comments based on a query
async def buscar(query):
    browser = await launch(args=['--no-sandbox'])  # abro el navegador chromium
    page = await browser.newPage()  # nueva pagina dentro del navegador
    await page.goto('https://www.tripadvisor.es/')  # busco la pagina web

    selector_search = "input._3qLQ-U8m"  # escribo la consulta del parametro
    await page.type(selector_search, query)
    await page.click('._2a_Ua4Qv')  # hago click en el boton de buscar
    await page.waitFor(3000)
    html = await page.evaluate('new XMLSerializer().serializeToString(document.doctype) + '
                               'document.documentElement.outerHTML')
    find_url(html)
    await browser.close()


def find_url(html):
    soup = BeautifulSoup(html, 'html.parser')
    main = soup.find('div', class_="main_content")

    if main is not None:
        first_result = main.findAll(True, {'class': ['ui_columns', 'is-mobile', 'result-content-columns']})
        main_result = first_result[1]
        first_result_2 = main_result.find(True, {'class': ['is-3-desktop', 'is-3-tablet', 'is-4-mobile', 'thumbnail-column']})

        enlace_f = first_result_2.get('onclick')
        enlace = re.findall('/.+html', enlace_f)  # regex101: '\/.+html'

        # llamar a encontrar las recomendaciones de esa pagina y luego imprimir todos los comentarios de cada pagina
        rutas = recommendations_url('https://www.tripadvisor.es' + str(enlace[0]))
        comments(rutas[1])


enlaces = []
nombres = []
def recommendations_url(pagina):
    # obtengo las recomendaciones de la ciudad consultada
    page = requests.get(str(pagina))
    soup = BeautifulSoup(page.content, 'html.parser')
    main = soup.find('div', id="content")  # obtengo el contenido central de la pagina
    if main is not None:
        containers = main.find_all('div', class_="ui_container")  # obtengo todos los contenedores de recomendacion
        for container in containers:
            items = container.find_all('div', class_="ui_column")
            for item in items:
                enlace = item.find('a', class_='ui_poi_thumbnail')
                nombre = item.find('span', class_='social-shelf-items-ShelfLocationSection__name--CdA_A')
                if enlace is not None:
                    href = enlace['href']  # enlace
                    enlaces.append(href)  # guardo los enlaces a cada actividad recomendada en el diccionario
                if nombre is not None:
                    nombres.append(nombre.get_text())
        print(len(enlaces))
        return nombres, enlaces


def comments(urls):
    i = 0  # contador para los nombres de los sitios
    for ruta in urls:
        page2 = requests.get('https://www.tripadvisor.es' + str(ruta))  # obtengo el enlace del sitio
        soup2 = BeautifulSoup(page2.content, 'html.parser')  # accedo al html de la pagina
        # obtengo todas las paginas de comentarios
        paginas = soup2.find('div', class_="pageNumbers")
        if paginas is not None:
            pag_enlaces = paginas.find_all('a', class_="pageNum")
            for link in pag_enlaces:
                pag_href = link['href']
                page3 = requests.get('https://www.tripadvisor.es' + str(pag_href))
                soup3 = BeautifulSoup(page3.content, 'html.parser')
                opiniones = soup3.find('div', id='REVIEWS')  # obtengo el container de opiniones
                if opiniones is not None:
                    comentarios = opiniones.find_all('div', class_='ui_column is-9')  # obtengo todos los comentarios
                    for comment in comentarios:
                        if comment is not None:
                            rating_date = comment.find('span', class_='ratingDate')
                            date = rating_date.get('title')  # tengo la fecha en formato "9 de enero de 2020"
                            reversed_date = ' '.join(reversed(date.split(' ')))  # anio-mes-dia
                            fecha = reversed_date.replace(' de ', '-')
                            tokens = re.split('-', fecha)
                            month_number = month_str_to_number(tokens[1])
                            final_date = tokens[0] + "-" + str(month_number) + '-' + tokens[2]
                            date_time_obj = datetime.strptime(final_date, '%Y-%m-%d')

                            title = comment.find('div', class_='quote').get_text()

                            text = comment.find('p', class_='partial_entry').get_text()
                            texto = text.replace('\n', ' ')

                            rating = comment.find('span', class_='ui_bubble_rating')
                            numero = rating.get('class')[1]
                            rate = float(numero.split('_')[1]) / 10.0

                            response = {
                                'date': str(date_time_obj.date()),  # anio, mes, dia
                                'place': unidecode(nombres[i]),
                                'title': unidecode(title),
                                'text': unidecode(texto),
                                'rating': rate
                            }
                            respuesta = json.dumps(response, ensure_ascii=False)
                            print(respuesta)
        i += 1


def month_str_to_number(month):
    months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']
    return months.index(month) + 1
