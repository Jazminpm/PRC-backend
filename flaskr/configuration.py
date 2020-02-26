# -*- encoding: utf-8 -*-

# Python modules
import os

# Grabs the folder where the script runs.
basedir = os.path.abspath(os.path.dirname(__file__))


class Config(object):
    """
    Base config, uses staging database server.
    """
    PORT = 5000
    ENV = "production"
    DEBUG = False
    TESTING = False
    API_VERSION = "beta"


class ProductionConfig(Config):
    DATABASE_URI = ""


class DevelopmentConfig(Config):
    DATABASE_URI = ""
    ENV = "development"
    DEBUG = True


class TestingConfig(Config):
    DATABASE_URI = ""
    ENV = "development"
    DEBUG = True
    TESTING = True
