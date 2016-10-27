#!/bin/bash -x
#sed -n 's/.*src="\([^"|?]*\).*/\1/p' ../../interface/html5/index.php | grep -i \.js | grep -v "\.php" | grep -v "require.js"
#sudo npm install -g grunt
#sudo npm install -g grunt-contrib-uglify
#sudo npm install -g grunt-contrib-concat
#sudo npm install grunt-contrib-uglify --save-dev
#sudo npm install grunt-contrib-concat --save-dev
#sudo npm install grunt-concat-css --save-dev
#sudo npm install grunt-contrib-cssmin --save-dev
grunt
