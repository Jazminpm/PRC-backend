# -*- encoding: utf-8 -*-

# Python modules
import os

# Flask modules
from flask import Flask
from flask_cors import CORS

# Grabs the folder where the script runs.
basedir = os.path.abspath(os.path.dirname(__file__))
app = Flask(__name__)
CORS(app)

from flaskr import routes

app.config.from_object('flaskr.configuration.DevelopmentConfig')
