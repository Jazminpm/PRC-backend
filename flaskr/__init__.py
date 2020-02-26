# -*- encoding: utf-8 -*-

# Python modules
import os

# Flask modules
from flask import Flask

# Grabs the folder where the script runs.
basedir = os.path.abspath(os.path.dirname(__file__))
app = Flask(__name__)

from flaskr import routes

app.config.from_object('flaskr.configuration.DevelopmentConfig')
