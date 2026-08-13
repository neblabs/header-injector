on plugin header:
    -version: gets the LAST versioned tag. Tag could be anything as long as it starts with vX.X.X , this includes unstable tags like alpha, beta, rc, you -literally- name it
on readme:
    - stable tag: gets the last STABLE tag in the repo in the form of: vX.X.X , any other unstable tag gets ignored eg: vX.X.X-rc-1, vX.X.X-beta, my-random-tag 

ci notes

if using actions checkout it may onlly fetch the latest tag which might be unreliable when dealing with multiple unstable tags.
recommended to use git fetch --tags before runnign this.