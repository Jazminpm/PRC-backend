import json
import sys
import pandas as pd  # CSV
import os

from models.utils import import_model

if __name__ == "__main__":
    os.chdir(os.path.dirname(__file__))
    data = json.loads(sys.argv[1])
    dataFrame = pd.read_csv('../../../modelsData/dataTrain.csv')
    import_model(data['algorithm_id'], dataFrame)
