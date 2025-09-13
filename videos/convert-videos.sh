#!/bin/bash

# convert out.ogv to mp4 and ogg

ffmpeg -i out.ogv -f mp4 out.mp4

ffmpeg -i out.ogv -f ogg out.ogg



