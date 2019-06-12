# ASYNC JavaScript

__If you care not for optimisation then shove all your JavaScript in the
'essential' folder and you will be fine__

This new system uses two folders instead of one. The 'essential' folders loads
scripts in the header like normal.

The 'deferred' folder on the other hand takes advantage of ASYNC so that any
files here will not load until after the rest of the page has first. This is
primarily for scripts which are not essential for the page straight away,
therefore allowing the user to use the website while these features are still
loading.

__JQUERY:__

- jQuery must be put in the essential folder as it is not possible to async.
