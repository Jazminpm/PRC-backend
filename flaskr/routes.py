# -*- encoding: utf-8 -*-

# Flask modules
from flask import Flask, jsonify, request, abort
from http import HTTPStatus

# App modules
from analysis.utils import textblob_analysis, vader_analysis
from flaskr import app


@app.route('/analysis/textblob', methods=['POST'])
def get_analysis_textblob():
    if not request.json or not 'msg' in request.json:
        abort(HTTPStatus.BAD_REQUEST)
    sentiment = textblob_analysis(request.json['msg'])

    return jsonify({
        'polarity': sentiment[0],
        'subjectivity': sentiment[1]
    }), HTTPStatus.OK


@app.route('/analysis/vader', methods=['POST'])
def get_analysis_vader():
    if not request.json or not 'msg' in request.json:
        abort(HTTPStatus.BAD_REQUEST)
    sentiment = vader_analysis(request.json['msg'])

    return jsonify({
        'polarity': sentiment[0],
        'subjectivity': None
    }), HTTPStatus.OK


@app.route('/analysis', methods=['POST'])
def get_analysis():
    if not request.json or not 'lib' or not 'msg' in request.json:
        abort(HTTPStatus.BAD_REQUEST)
    if request.json['lib'] is 1:
        sentiment = textblob_analysis(request.json['msg'])
    elif request.json['lib'] is 2:
        sentiment = vader_analysis(request.json['msg'])
    else:
        abort(HTTPStatus.BAD_REQUEST)

    return jsonify({
        'polarity': sentiment[0],
        'subjectivity': sentiment[1]
    }), HTTPStatus.OK
