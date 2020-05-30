import json
import sys
import pandas as pd  # CSV
import os

from models.utils import prediction

if __name__ == "__main__":
    os.chdir(os.path.dirname(__file__))
    data = json.loads(sys.argv[1])
    dataFrame = pd.read_csv('../../../storage/modelsData/dataPredict.csv')
    prediction(data['0'], data['1'], dataFrame)
