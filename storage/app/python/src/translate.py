# -*- encoding: utf-8 -*-

import json
import sys

from analysis.utils import translate

if __name__ == "__main__":
    data = json.loads(sys.argv[1])

    response = {
        'translation': translate(msg=data['msg'], dest=data['lang']),
    }

    print(json.dumps(response))
