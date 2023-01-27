# python3.9 send.py 
# https://github.com/engrbm87/notifications_android_tv
from notifications_android_tv import Notifications


# with open("icon.png", "rb") as image:
with open("../lastpicture.jpg", "rb") as image:
  f = image.read()
  b = bytearray(f)

notify = Notifications("192.168.86.58")
notify.send(
    "Jemand hat an der Eingangstür geklingelt",
    title="Loxone",
    fontsize="medium",
    bkgcolor="green",
    image_file=b
)

# pip3 install notifications-android-tv