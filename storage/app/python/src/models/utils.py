# -*- encoding: utf-8 -*-
# Algorithms
from sklearn.naive_bayes import GaussianNB
from sklearn.ensemble import RandomForestClassifier
from sklearn.ensemble import GradientBoostingClassifier
from sklearn.neighbors import KNeighborsClassifier
from sklearn import tree
from sklearn.linear_model import LogisticRegression

# Preprocess data
from collections import defaultdict
from sklearn import preprocessing

from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report  # Report

# Import and export
import pickle  # models
from pathlib import Path  # know the actual route
import pandas as pd  # CSV

# Datetime for the models
from datetime import datetime
import os

def import_model(characteristic, select_classifier):
    os.chdir(os.path.dirname(__file__))
    pd.set_option('mode.chained_assignment', None)
    data_train = pd.read_csv('dataset_TRAIN.csv', delimiter=";",
                             encoding="ISO-8859-1")  # encoding = "ISO-8859-1" --> Permite importar caracteres especiales

    # -------------1. Select characteristic and label
    label = data_train['delay']
    data_train = data_train.drop(['delay'], axis=1)

    for key in list(data_train.head()):
        if key not in characteristic:
            data_train = data_train.drop([key], axis=1)

    # -------------2. Preparing Data For Training (divides data into attributes and labels)
    df_object = data_train.select_dtypes(include=[object])
    keys_object = list(df_object.head())
    df_no_object = data_train.select_dtypes(exclude=[object])
    for c in range(len(df_object.columns)):  # variables categoricas y ultima columna con valores unknown y 0
        df_object[keys_object[c]][len(df_object) - 1] = "unknown"
        df_object[keys_object[c]] = df_object[keys_object[c]].astype('category')

    d = defaultdict(preprocessing.LabelEncoder)
    df_object = df_object.apply(
        lambda x: d[x.name].fit_transform(x))  # Transformamos los datos a valores numericos

    if df_no_object.empty is not True and df_object.empty is not True:
        data_train = df_object
        data_train = data_train.join(df_no_object, how='outer')
    elif not df_object.empty:
        data_train = df_object
    else:
        data_train = df_no_object

    # -------------3. Create variables for train
    X = data_train.values
    y = label.values  # label es retrasado
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.20)

    # -------------4. Select, create and train the Algorithm
    if select_classifier == 0:
        classifier = GaussianNB()
        model_name = 'naive bayes'
    elif select_classifier == 1:
        classifier = RandomForestClassifier(n_estimators=3,
                                            random_state=0)
        model_name = 'random forest'
    elif select_classifier == 2:
        classifier = GradientBoostingClassifier(n_estimators=3, learning_rate=0.5, max_features=2, max_depth=2,
                                                random_state=0)
        model_name = 'gradient boosting classifier'
    elif select_classifier == 3:
        classifier = tree.DecisionTreeClassifier()
        model_name = 'decision tree'
    elif select_classifier == 4:
        classifier = KNeighborsClassifier()
        model_name = 'k-nn'
    else:
        classifier = LogisticRegression(solver='liblinear')
        model_name = 'logistic regression'
    classifier.fit(X_train, y_train)

    # -------------5. Export model, report and selected columns
    y_pred = classifier.predict(X_test)
    report = pd.DataFrame(classification_report(y_test, y_pred, output_dict=True)).transpose()
    response = {
        'type': str(model_name),
        'date': str(datetime.now().strftime("%Y-%m-%d %H:%M:%S")),
        'report_n_rows': len(data_train),
        'report_precision_0': str(report['precision'][0]),
        'report_precision_1': str(report['precision'][1]),
        'report_recall_0': str(report['recall'][0]),
        'report_recall_1': str(report['recall'][1]),
        'report_f1_score_0': str(report['f1-score'][0]),
        'report_f1_score_1': str(report['f1-score'][1]),
        'report_accuracy_precision': str(report['precision'][2]),
        'report_accuracy_recall': str(report['recall'][2]),
        'report_accuracy_f1_score': str(report['f1-score'][2]),
        'attribute_date': int('date' in characteristic),
        'attribute_time': int('expected_departure_time' in characteristic),
        'attribute_id': int('id' in characteristic),
        'attribute_airline': int('airline' in characteristic),
        'attribute_destination': int('destination' in characteristic),
        'attribute_temperature': int('temperature' in characteristic),
        'attribute_humidity': int('humidity' in characteristic),
        'attribute_wind': int('wind' in characteristic),
        'attribute_wind_direction': int('wind_direction' in characteristic),
        'attribute_wind_pressure': int('pressure' in characteristic)
    }
    print(response)

    save_model(classifier, model_name, d)


def save_model(classifier, model_name, le):
    root = Path("..") / Path("..")
    now = datetime.now()
    # SAVE PROCESADOR DE CARACTERÍSTICAS
    filenameSAV = model_name + " " + str(now.date()) + " " + str(now.hour) + "." + str(now.minute) + "." + str(
        now.second) + ".pkl"

    my_path = root / "Model" / "preprocess characteristic" / model_name / filenameSAV
    my_file = open(my_path, 'wb')  # Open file for writhing
    pickle.dump(le, my_file)

    # SAVE SAV
    filenameSAV = model_name + " " + str(now.date()) + " " + str(now.hour) + "." + str(now.minute) + "." + str(
        now.second) + ".sav"
    my_path = root / "model" / "training models" / model_name / filenameSAV
    my_file = open(my_path, 'wb')
    pickle.dump(classifier, my_file)


def prediction(characteristic, model_name, model_date):
    os.chdir(os.path.dirname(__file__))

    # 1. Import from directories
    date = datetime.strptime(model_date, "%Y-%m-%d %H:%M:%S")
    filename = model_name + " " + str(date.date()) + " " + str(date.hour) + "." + str(date.minute) + "." + str(
        date.second)

    model_path = Path("..") / Path("..") / "model" / "training models" / model_name / (filename + ".sav")

    with open(model_path, 'rb') as pickle_file:
        model = pickle.load(pickle_file)

    preprocess_path = Path("..") / Path("..") / "model" / "preprocess characteristic" / model_name / (filename + ".pkl")
    preprocess_obj = pickle.load(open(preprocess_path, 'rb'))

    # 2. Import data for test
    pd.set_option('mode.chained_assignment', None)
    data_test = pd.read_csv('dataset_test.csv', delimiter=";",
                            encoding="ISO-8859-1")
    for key in list(data_test.head()):
        if key not in characteristic:
            data_test = data_test.drop([key], axis=1)

    # -------------2. Preparing Data For Training (divides data into attributes and labels)
    df_object = data_test.select_dtypes(include=[object])
    keys_object = list(df_object.head())
    df_no_object = data_test.select_dtypes(exclude=[object])

    for f in range(len(df_object)):
        for c in range(len(df_object.columns)):
            if df_object[keys_object[c]][f] not in preprocess_obj[keys_object[c]].classes_:
                df_object[keys_object[c]][f] = "unknown"

    # Transform all to numbers with import preprocess object
    df_object = df_object.apply(lambda x: preprocess_obj[x.name].transform(x))

    df_object = df_object.join(df_no_object, how='outer')
    X = df_object.values
    prediction = model.predict(X)
    print(prediction)
