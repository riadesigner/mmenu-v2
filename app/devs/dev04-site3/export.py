import os, sys
import shutil

# =============================================================
# this app 
# 1. get number of App-version as string (arg[1])
# 2. make folder ../export (if no exist)
# 3. verify if folder ../export/exp-v-App-version is exist 
#    and do App-version+"1" if true
# 4. copy current folder to ../export/exp-v-App-version
# 5. while copy its exception files and folders from exceptList
# =============================================================

exceptList = [
	'export.py',
	'node_modules',
	'./adm/src',
	'./pbl/src',
	'./site/src',
	'package-lock.json',
	'rollup.config.mjs',
	'prod-rollup.config.js',
	'package.json'
	]

# get number of app-version
# for example: 1-101
try:	
	ver = sys.argv[1]
except:	
	print("Err: Hei, you fogot give a number of version.")
	print("for example: "+sys.argv[0]+" 1-101")

# create folder name 
dest = "app-v"+ver
dest = os.path.abspath("../../export/"+dest) 

# create folder if not exitst
# stop if already exist 
if os.path.exists(dest):
	print("Stop: you have already "+ver)
	print("in "+dest)
	sys.exit()
else:
	os.makedirs(dest)

# copy all into dest folder
def recursive_copy(src, dest):
    for item in os.listdir(src):
        file_path = os.path.join(src, item)
        avoid_item = item in exceptList
        avoid_path = file_path in exceptList        
        # if item is a file, copy it
        if os.path.isfile(file_path):
        	if not avoid_item:
        	    shutil.copy(file_path, dest)
        # else if item is a folder, recurse 
        elif os.path.isdir(file_path) and not avoid_item and not avoid_path:
	        new_dest = os.path.join(dest, item)
	        os.mkdir(new_dest)
	        recursive_copy(file_path, new_dest)	

recursive_copy(".",dest)




