# Language file specifications

All texts used in this SDK will be loaded from CSV (Comma Separated Values) files. We use CSV format because files are easily editable using Excel or equivalent applications.

First column should not be edited at all as it is used as "text identifier" for language system. Second column represents the translation of first column in language defined by the file.

**NOTE FOR WINDOWS SERVERS**: as Windows machine doesn't have a native solution for converting UTF-16 files to UTF-8 files, you will have to convert {lang}.csv file to UTF-8 encoding into {lang}-utf8.csv file.

System will ensure all csv files are UTF-8 encoded, that's why you will see {lang}-utf8.csv files (eg. en-utf8.csv). These files are UTF-8 encoded version of you original csv file. 
When you want to change language texts don't edit these {lang}-utf8.csv file, but the original {lang}.csv file. System will always assume {lang}-utf8.csv file is UTF-8 encoding of original file
  and will do no checks to see if original {lang}.csv file is UTF-8 encoded and will overwrite {lang}-utf8.csv files when converting them to UTF-8 encoding, so please don't alter these files.
  If, however you do edit these files, you can safely remove it as system will generate new one if {lang}-utf8.csv file is not found.

.
