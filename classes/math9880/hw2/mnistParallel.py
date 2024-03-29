"""
Created on Fri Dec  6 11:56:46 2019

@author: apangia
"""
#Basically copied from
#https://www.tensorflow.org/tutorials/distribute/keras

import tensorflow;
from tensorflow import keras;

import numpy as np;
import matplotlib.pyplot as plt;

#package for saving models to use after using DGX
import os;

#import the MNIST data from tensorflow
mnist=keras.datasets.mnist;

(trainImages, trainLabels), (testImages, testLabels)= \
                                        mnist.load_data();

gurkeImageTrain=trainImages/255.0;
gurkeImageTest=testImages/255.0;

#force the images to greyscale and assign a channel to them so Conv2D works
trainImages= np.expand_dims(gurkeImageTrain,axis=3);
testImages= np.expand_dims(gurkeImageTest, axis=3);

#create datasets of the images so we can batch them
mnistTrain=tensorflow.data.Dataset.from_tensor_slices((trainImages,
                                                       trainLabels));
mnistTest=tensorflow.data.Dataset.from_tensor_slices((testImages,
                                                      testLabels));

strategy=tensorflow.distribute.MirroredStrategy();
print('Number of devices seen: {}'.format(strategy.num_replicas_in_sync));
file=open("numDevices.txt",'w');
file.write('Number of devices seen: {}'.format(strategy.num_replicas_in_sync
           ));
file.close();

bufferSize=10000;

batchSizePerReplica=64;
batchSize=batchSizePerReplica*strategy.num_replicas_in_sync;

#def scale(image, label):
#    image=tensorflow.cast(image,tensorflow.float32);
#    return image, label;

trainDataset=mnistTrain.cache().shuffle(bufferSize).batch(
        batchSize);
testDataset=mnistTest.batch(bufferSize);

with strategy.scope():
    model=tensorflow.keras.Sequential([
            tensorflow.keras.layers.Conv2D(32, 3, \
                activation='relu', input_shape=(28, 28, 1)),
            tensorflow.keras.layers.MaxPooling2D(),
            tensorflow.keras.layers.Flatten(),
            tensorflow.keras.layers.Dense(64, activation='relu'),
            tensorflow.keras.layers.Dense(10, activation='softmax')]);
#    model=tensorflow.keras.Sequential([
#            tensorflow.keras.layers.Conv2D(128, 3, \
#                activation='relu', input_shape=(28, 28, 1)),
#            tensorflow.keras.layers.Conv2D(64, 3),
#            tensorflow.keras.layers.Conv2D(64, 3),
#            tensorflow.keras.layers.MaxPooling2D((2,2)),
#            tensorflow.keras.layers.Conv2D(32, 3),
#            tensorflow.keras.layers.Conv2D(32,2),
#            tensorflow.keras.layers.Conv2D(32,2),
#            tensorflow.keras.layers.MaxPooling2D((2,2)),
#            tensorflow.keras.layers.Flatten(),
#            tensorflow.keras.layers.Dense(64, activation='relu'),
#            tensorflow.keras.layers.Dense(10, activation='softmax')]);
model.summary()
model.compile(loss='sparse_categorical_crossentropy',
                optimizer=tensorflow.keras.optimizers.Adam(),
                metrics=['accuracy']);

#save the checkpoints to load them later
checkpoint_dir = './training_checkpoints';
# Name of the checkpoint files
checkpoint_prefix = os.path.join(checkpoint_dir, "ckpt_{epoch}")

#change the decay as the epochs progress
def decay(epoch):
  if epoch < 3:
    return 1e-3;
  elif epoch >= 3 and epoch < 7:
    return 1e-4;
  else:
    return 1e-5;
#end decay

# Callback for printing the LR at the end of each epoch.
class PrintLR(tensorflow.keras.callbacks.Callback):
  def on_epoch_end(self, epoch, logs=None):
    print('\nLearning rate for epoch {} is {}'.format(epoch + 1,
                 model.optimizer.lr.numpy()));
    file.write('\nLearning rate for epoch {} is {}'.format(epoch + 1,
                 model.optimizer.lr.numpy()));
  #end onEpochEnd
#end PrintLR

#set up things for the model to do; like what learning rate to use,
#where the logs are saved, etc.
#callbacks = [tensorflow.keras.callbacks.TensorBoard(log_dir='./logs'),
#    tensorflow.keras.callbacks.ModelCheckpoint(filepath=checkpoint_prefix,
#                                       save_weights_only=True),
#    tensorflow.keras.callbacks.LearningRateScheduler(decay),
#    PrintLR()];
callbacks = [
    tensorflow.keras.callbacks.LearningRateScheduler(decay),
    PrintLR()];

file=open("learningRate.txt",'w');
#run the model
model.fit(trainDataset, epochs=10, callbacks=callbacks);
#model.fit(trainDataset, epochs=12);
file.close();

#export to a save file; CHANGE THIS TO SOMETHING WE ACTUALLY WANT
path = 'savedModel/';

model.save(path, save_format='tensorflow');

evalLoss, evalAcc = model.evaluate(testDataset);
predictions=model.predict(testDataset);


print('Eval loss: {}, Eval Accuracy: {}'.format(evalLoss, evalAcc));
file=open("testAccuracy.txt",'w');
file.write('Eval loss: {}, Eval Accuracy: {}'.format(evalLoss, evalAcc));
file.close();


#create these to make plots easier
def plotImage(i, predictionsArray, trueLabel, img):
  predictionsArray, trueLabel, img = predictionsArray, trueLabel[i], img[i];
  plt.grid(False);
  plt.xticks([]);
  plt.yticks([]);

  plt.imshow(img, cmap=plt.cm.binary);

  predictedLabel = np.argmax(predictionsArray);
  if predictedLabel == trueLabel:
    color = 'blue';
  else:
    color = 'red';

  plt.xlabel("{} {:2.0f}% ({})".format(classNames[predictedLabel],
                                100*np.max(predictionsArray),
                                classNames[trueLabel]),
                                color=color);

def plotValueArray(i, predictionsArray, trueLabel):
  predictionsArray, trueLabel = predictionsArray, trueLabel[i];
  plt.grid(False);
  plt.xticks(range(10));
  plt.yticks([]);
  thisplot = plt.bar(range(10), predictionsArray, color="#777777");
  plt.ylim([0, 1]);
  predictedLabel = np.argmax(predictionsArray);

  thisplot[predictedLabel].set_color('red');
  thisplot[trueLabel].set_color('blue');

classNames=['zero','one','two','three', 'four','five', 'zix',\
             'zeven','eight','nine'];

#plot the first eight figures from the test data
numRows = 4;
numCols = 2;
numImages = numRows*numCols;
plt.figure(figsize=(2*2*numCols, 2*numRows));
for i in range(numImages):
  plt.subplot(numRows, 2*numCols, 2*i+1);
  plotImage(i, predictions[i], testLabels, gurkeImageTest);
  plt.subplot(numRows, 2*numCols, 2*i+2);
  plotValueArray(i, predictions[i], testLabels);
plt.tight_layout();
plt.savefig('examples.png');
plt.show()

#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
#
##load the data without strategy.scope
#path= 'savedModel/';
#unreplicatedModel = tensorflow.keras.models.load_model(path)
#
###load without strategy scope
##unreplicatedModel.compile(
##    loss='sparse_categorical_crossentropy',
##    optimizer=tensorflow.keras.optimizers.Adam(),
##    metrics=['accuracy'])
##
##evalLoss, evalAcc = unreplicatedModel.evaluate(testDataset);
##
##print('Eval loss: {}, Eval Accuracy: {}'.format(evalLoss, evalAcc));
#
##make predictions
#predictions=unreplicatedModel.predict(testDataset);
#
##load with strategy scope
#with strategy.scope():
#    replicatedModel=tensorflow.keras.models.load_model(path);
#    replicatedModel.compile(loss='sparse_categorical_crossentropy',
#                            optimizer=tensorflow.keras.optimizers.Adam(),
#                            metrics=['accuracy']);
#
#evalLoss, evalAcc = replicatedModel.evaluate(testDataset);
#
#print('Eval loss: {}, Eval Accuracy: {}'.format(evalLoss, evalAcc));
#
#
##explore data here
#gurkeImageTrain.shape
#len(trainLabels)
#trainLabels
##numTrainExamples=gurkeImageTrain.shape[0];
#
#gurkeImageTest.shape
#len(testLabels)
#numTestExamples=gurkeImageTest.shape[0];
#
#classNames=['zero','one','two','three', 'four','five', 'zix',\
#             'zeven','eight','nine'];
#
##preprocess the data
#plt.figure()
#plt.imshow(gurkeImageTrain[0])
#plt.colorbar()
#plt.grid(False)
#plt.show()
#
##set as greyscale
##train_images= train_images/255.0;
##test_images= test_images/255.0;
#
##display training data images
#plt.figure(figsize=(10,10));
#for i in range(25):
#    plt.subplot(5,5,i+1);
#    plt.xticks([]);
#    plt.yticks([]);
#    plt.grid(False);
#    plt.imshow(gurkeImageTrain[i], cmap=plt.cm.binary);
#    plt.xlabel(classNames[trainLabels[i]]);
#    #plt.xlabel(train_labels[i]);
#plt.show()
#
##display testing data images
#plt.figure(figsize=(10,10))
#for i in range(25):
#    plt.subplot(5,5,i+1)
#    plt.xticks([])
#    plt.yticks([])
#    plt.grid(False)
#    plt.imshow(gurkeImageTest[i], cmap=plt.cm.binary)
#    plt.xlabel(classNames[testLabels[i]])
#plt.show()
#
##check the checkpoint directory
#!ls {checkpoint_dir}
#
#model.load_weights(tensorflow.train.latest_checkpoint(checkpoint_dir));
#
#evalLoss, evalAcc = model.evaluate(testDataset);
#
#print('Eval loss: {}, Eval Accuracy: {}'.format(evalLoss, evalAcc));



