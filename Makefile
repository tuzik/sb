#NJR Makefile
#by Gaffey Cai
#email: caijingming@staff.139.com
#

CLASSES=$(wildcard *.class.php)
BASE=NJ.php
NJR=/usr/share/NJR/
RM=rm -rf
MKDIR=mkdir -p

.PHONY:clean build install

clean:
	$(RM) build/

build:
	@$(MKDIR) build/NJ/
	@cp $(CLASSES) build/NJ/
	cp $(BASE) build/

install:
	@$(MKDIR) $(NJR)
	@cp -rf build/ $(NJR)
	echo "NJR has been installed in /usr/share/NJR"