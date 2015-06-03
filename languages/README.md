# Language file specifications

All texts used in this SDK will be loaded from CSV (Comma Separated Values) files. We use CSV format because files are easily editable using Excel or equivalent applications.

First column should not be edited at all as it is used as "text identifier" for language system. Second column represents the translation of first column in language defined by the file.

System will ensure all csv files are UTF-8 encoded, that's why you will see {lang}-utf8.csv files (eg. en-utf8.csv) These files are UTF-8 encoded version of you original csv file. 
When you want to change language texts don't edit these {lang}-utf8.csv file, but the original {lang}.csv file. System will always assume {lang}-utf8.csv file is UTF-8 encoding of original file
  and will do no checks to see if it is UTF-8 encoded, so please don't alter these files. If, however you do edit these files, you can safely remove it as system will generate new one
  if {lang}-utf8.csv file is not found.

.