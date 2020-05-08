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

#Print model
import json

def import_model(select_classifier, data_train):
    os.chdir(os.path.dirname(__file__))
    characteristic = list(data_train.head())

    # -------------1. Select characteristic and label
    # -------------1. Select characteristic and label
    label = data_train['delay']
    data_train = data_train.drop(['delay'], axis=1)

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
    if select_classifier == 1:
        classifier = GaussianNB()
        model_name = 'naive_bayes'
    elif select_classifier == 2:
        classifier = RandomForestClassifier(n_estimators=3,
                                            random_state=0)
        model_name = 'random_forest'
    elif select_classifier == 3:
        classifier = GradientBoostingClassifier(n_estimators=3, learning_rate=0.5, max_features=2, max_depth=2,
                                                random_state=0)
        model_name = 'gradient_boosting'
    elif select_classifier == 4:
        classifier = tree.DecisionTreeClassifier()
        model_name = 'decision_tree'
    elif select_classifier == 5:
        classifier = KNeighborsClassifier()
        model_name = 'k-nn'
    else:
        classifier = LogisticRegression(solver='liblinear')
        model_name = 'logistic_regression'
    classifier.fit(X_train, y_train)

    # -------------5. Export model, report and selected columns
    y_pred = classifier.predict(X_test)

    save_model(classifier, model_name, d)

    report = pd.DataFrame(classification_report(y_test, y_pred, output_dict=True)).transpose()
    response = {
        'type': select_classifier,
        'date': str(datetime.now().strftime("%Y-%m-%d %H:%M:%S")),
        'report_num_rows': len(data_train),
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
        'attribute_time': int('time' in characteristic),
        'attribute_id': int('id' in characteristic),
        'attribute_airline': int('airline_id' in characteristic),
        'attribute_destination': int('city_id' in characteristic),
        'attribute_temperature': int('temperature' in characteristic),
        'attribute_humidity': int('humidity' in characteristic),
        'attribute_wind_speed': int('wind_speed' in characteristic),
        'attribute_wind_direction': int('wind_direction' in characteristic),
        'attribute_pressure': int('pressure' in characteristic),
        'attribute_airport_id': int('airport_id' in characteristic)
    }
    print(json.dumps(response))


def save_model(classifier, model_name, le):
    now = datetime.now()
    # SAVE PROCESADOR DE CARACTERÍSTICAS
    filenameSAV = model_name + "_" + str(now.date()) + "_" + str(now.hour) + "." + str(now.minute) + "." + str(
        now.second) + ".pkl"
    my_path = Path("..") / Path("..") / Path("..") / Path("..") / "models" / "preprocess_characteristic" / model_name / filenameSAV
    my_file = open(my_path, 'wb')  # Open file for writhing
    pickle.dump(le, my_file)

    # SAVE SAV
    filenameSAV = model_name + "_" + str(now.date()) + "_" + str(now.hour) + "." + str(now.minute) + "." + str(
        now.second) + ".sav"
    my_path = Path("..") / Path("..") / Path("..") / Path("..") / "models" / "training_models" / model_name / filenameSAV
    my_file = open(my_path, 'wb')
    pickle.dump(classifier, my_file)


def prediction(num_model, model_date, data_test):
    os.chdir(os.path.dirname(__file__))

    model_name = ['', 'naive_bayes', 'random_forest', 'gradient_boosting', 'decision_tree', 'k-nn',
    'logistic_regression']

    # 1. Import from directories
    date = datetime.strptime(model_date, "%Y-%m-%d %H:%M:%S")
    filename = str(model_name[num_model]) + "_" + str(date.date()) + "_" + str(date.hour) + "." + str(date.minute) + "." + str(
        date.second)

    model_path = Path("..") / Path("..") / Path("..") / Path("..") / "models" / "training_models" / str(model_name[num_model]) / (filename + ".sav")
    with open(model_path, 'rb') as pickle_file:
        model = pickle.load(pickle_file)

    preprocess_path = Path("..") / Path("..") / Path("..") / Path("..") / "models" / "preprocess_characteristic" / str(model_name[num_model]) / (filename + ".pkl")
    preprocess_obj = pickle.load(open(preprocess_path, 'rb'))

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
