# call: octave wav2mat.m two 8000 128

warning ("off"); # disable warning messages

args = argv();

filename = args{1};   #'two';
rate = args{2};       #'8000';
decimation = args{3}; #'128';

infile = strcat(filename, '.wav');

y = wavread(infile);

spec=LyonPassiveEar(y, str2num(rate), str2num(decimation)); 

outfile = strcat(filename, '.mat');
 
save(outfile,'spec','-7');

