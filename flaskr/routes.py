# -*- encoding: utf-8 -*-

# Flask modules

from flask import jsonify
from http import HTTPStatus

# App modules
from flaskr import app


@app.route('/version', methods=['GET'])
def api_version():
    """
    Checks the API version.
    :return: returns the API version.
    """
    return jsonify({
        'version': app.config['API_VERSION']
    }), HTTPStatus.OK
