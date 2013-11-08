#!/bin/bash
phpdoc -d . -t docs --ignore=make-doc.sh --ignore vendor/ --ignore plugins/ --ignore docs/ --ignore locales/ --ignore templates/ --template=new-black