import json
import sys

from analysis.utils import textblob_analysis, vader_analysis, textblob_comment, translate

if __name__ == "__main__":
    data = json.loads(sys.argv[1])
    traduccion = str(translate(data["msg"], 'en'))

    sentiment = textblob_comment(traduccion)
    response = {
        'polarity': sentiment[0],
        'subjectivity': sentiment[1],
        'message': traduccion
    }

    print(json.dumps(response))
