import json
import sys

from analysis.utils import textblob_analysis, vader_analysis

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
