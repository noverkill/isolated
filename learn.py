import sys

sys.path.append('C:\wamp\www\recorder')
sys.path.append('C:\Python27\lib\site-packages\speech-0.5.2-py2.7.egg')
sys.path.append('C:\Windows\system32\python27.zip')
sys.path.append('C:\Python27\DLLs')
sys.path.append('C:\Python27\lib')
sys.path.append('C:\Python27\lib\plat-win')
sys.path.append('C:\Python27\lib\lib-tk')
sys.path.append('C:\Python27')
sys.path.append('C:\Users\szilard\AppData\Roaming\Python\Python27\site-packages')
sys.path.append('C:\Python27\lib\site-packages')
sys.path.append('C:\Python27\lib\site-packages\win32')
sys.path.append('C:\Python27\lib\site-packages\win32\lib')
sys.path.append('C:\Python27\lib\site-packages\Pythonwin')
sys.path.append('C:\Python27\lib\site-packages\setuptools-0.6c11-py2.7.egg-info')

import os, shutil
import getopt, pylab, mdp
from scipy.io import loadmat
from mdp import numx, utils
from mdp.utils import mult
import numpy as np
import Oger
import pickle

#def guess(input, reservoir, readout, dirname):
def guess(input, reservoir, dirname):
	
    #print input.shape
    
    """
    pylab.plot(input)
    pylab.show()			
    pylab.figure()
    """
    
    try:
         #readout.beta = np.loadtxt(dirname + '\\beta.mat')
        beta = np.loadtxt(dirname + '\\beta.mat')
    except:
        return 0 #19
        
    x = reservoir.execute(input)

    #m = readout._execute(x)
    #m = mult(x, readout.beta)
    m = mult(x, beta)
        
    # find maximum place of m
    mcs = np.zeros(m.shape[1])

    for i in range(m.shape[1]):
        mc = sum(m[:,i]) / m.shape[1]
        mcs[i] = mc 

    return mcs.argmax()
    
def main(argv):
    inputfile = None
    number = None

    try:
      opts, args = getopt.getopt(argv,"hi:n:")
    except getopt.GetoptError:
      print 'test.py -i <inputfile>'
      sys.exit(2)
    
    for opt, arg in opts:
      if opt == '-h':
         print 'test.py -i <inputfile>'
         sys.exit()
      elif opt in ("-i"):
         inputfile = arg
      elif opt in ("-n"):
         number = arg
         #print number
         #sys.exit()

    #print 'Input file is "', inputfile
    
    dirname= inputfile.rsplit('\\',1)[0]    
    #print dirname
        
    if inputfile is None:
        print 'no input file'
        sys.exit()

    test_sample_file = inputfile
                
    if number is None:
        
        cwd = os.getcwd()

        os.chdir('C:\matlab\AuditoryToolbox\octave')

        #cmd = "octave -q wav2mat.m " + test_dir + '\\' + test_sample_file + " 8000 128 > null"
        cmd = "octave -q wav2mat.m " + test_sample_file + " 8000 128 > null"

        os.system(cmd)

        os.chdir(cwd)

    content = loadmat(test_sample_file + ".mat")

    test_input = content['spec'].T

    input_dim = test_input.shape[1]
    
    #readout = Oger.nodes.RidgeRegressionNode(use_pinv=True, input_dim=100)
    
    try:
        pinput = open(dirname + '\\reservoir.pkl', 'rb')
        reservoir = pickle.load(pinput)
        pinput.close()
    except IOError:
        reservoir = Oger.nodes.LeakyReservoirNode(input_dim=input_dim, output_dim=100, input_scaling=.1, leak_rate=.3)
        poutput = open(dirname + '\\reservoir.pkl', 'wb')
        pickle.dump(reservoir, poutput)
        poutput.close()
    
    if number is None:
        #gn = guess(test_input, reservoir, readout, dirname);    
        gn = guess(test_input, reservoir, dirname);    
        print gn
        sys.exit()

    #shutil.copy(test_sample_file + ".mat", test_sample_file + "_" + numba + ".mat")
        
    x = reservoir.execute(test_input)

    teacher_inputs = [test_input]

    teacher_outputs = [-1 * mdp.numx.ones([teacher_inputs[-1].shape[0], 10])]

    teacher_outputs[-1][:, number] = 1

    """
    readout._check_train_args(x, *teacher_outputs)
    
    readout.train(x, *teacher_outputs)

    readout._stop_training()
    """
    
    #if readout._xTx is None:
    try:
        xTx = np.loadtxt(dirname + '\\xTx.mat')       
        xTy = np.loadtxt(dirname + '\\xTy.mat')       
    except IOError:
        input_dim  = 100
        #readout._set_output_dim(10)
        output_dim = 10        
        #readout._dtype
        dtype = "float64"
        x_size = input_dim
        #readout._xTx = numx.zeros((x_size, x_size), dtype)
        xTx = numx.zeros((x_size, x_size), dtype)
        #readout._xTy = numx.zeros((x_size, output_dim), dtype)
        xTy = numx.zeros((x_size, output_dim), dtype)
                
    # update internal variables
    #readout._xTx += mult(x.T, x)
    xTx += mult(x.T, x)
    #readout._xTy += mult(x.T, *teacher_outputs)
    xTy += mult(x.T, *teacher_outputs)

    # calculate beta
    #inv_xTx = utils.inv(readout._xTx)   
    inv_xTx = utils.inv(xTx)   
    #readout.beta = mult(inv_xTx, readout._xTy)
    beta = mult(inv_xTx, xTy)
    
    # save everything to file
    """
    np.savetxt(dirname + '\\xtx.mat'   , readout._xTx)
    np.savetxt(dirname + '\\xty.mat'   , readout._xTy)
    np.savetxt(dirname + '\\invxtx.mat', inv_xTx)
    np.savetxt(dirname + '\\beta.mat'  , readout.beta)
    """
        
    np.savetxt(dirname + '\\xtx.mat'   , xTx)
    np.savetxt(dirname + '\\xty.mat'   , xTy)
    np.savetxt(dirname + '\\beta.mat'  , beta)
    
    print 99
                       
if __name__ == "__main__":
   main(sys.argv[1:])
