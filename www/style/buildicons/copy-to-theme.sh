#!/bin/bash

SIZE="16x16"
FROM=$SIZE

# copy selected icons to where they are needed for the webapp
# commandbar
TO="../commands/$SIZE"
mkdir $TO
cp "$FROM/actions/up.png" "$TO/parent.png"
cp "$FROM/actions/attach.png" "$TO/attach.png"
cp "$FROM/actions/messagebox_info.png" "$TO/showlog.png"
cp "$FROM/filesystems/folder_whitelinen_open.png" "$TO/createfolder.png"
cp "$FROM/actions/stop.png" "$TO/logout.png"
cp "$FROM/actions/back.png" "$TO/back.png"
cp "$FROM/actions/gohome.png" "$TO/home.png"
cp "$FROM/actions/reload.png" "$TO/refresh.png"
#  repository
TO="../repository/$SIZE"
mkdir $TO
cp "$FROM/filesystems/folder_banana.png" "$TO/folder.png"
cp "$FROM/mimetypes/mime.png" "$TO/file.png"
#  filetypes
TO="../repository/filetypes/$SIZE"
mkdir $TO
cp "$FROM/mimetypes/archive_zip.png" "$TO/zip.png"
cp "$FROM/mimetypes/archive_gz.png" "$TO/gz.png"
cp "$FROM/mimetypes/archive_sit.png" "$TO/sit.png"
cp "$FROM/mimetypes/chm.png" "$TO/chm.png"
cp "$FROM/mimetypes/exec_wine.png" "$TO/exe.png"
cp "$FROM/mimetypes/html.png" "$TO/html.png"
cp "$FROM/mimetypes/html.png" "$TO/htm.png"
cp "$FROM/mimetypes/msword_doc.png" "$TO/doc.png"
cp "$FROM/mimetypes/image_ai.png" "$TO/ai.png"
cp "$FROM/mimetypes/image_bmp.png" "$TO/bmp.png"
cp "$FROM/mimetypes/image_gif.png" "$TO/gif.png"
cp "$FROM/mimetypes/image_jpeg.png" "$TO/jpg.png"
cp "$FROM/mimetypes/image_png.png" "$TO/png.png"
cp "$FROM/mimetypes/image_psd.png" "$TO/psd.png"
cp "$FROM/mimetypes/image_tiff.png" "$TO/tif.png"
cp "$FROM/mimetypes/library.png" "$TO/jar.png"
cp "$FROM/mimetypes/log.png" "$TO/log.png"
cp "$FROM/mimetypes/pdf.png" "$TO/pdf.png"
cp "$FROM/mimetypes/postscript.png" "$TO/ps.png"
cp "$FROM/mimetypes/quicktime.png" "$TO/qt.png"
cp "$FROM/mimetypes/shellscript.png" "$TO/sh.png"
cp "$FROM/mimetypes/source_java.png" "$TO/java.png"
cp "$FROM/mimetypes/source_php.png" "$TO/php.png"
cp "$FROM/mimetypes/temp.png" "$TO/tmp.png"
cp "$FROM/mimetypes/txt.png" "$TO/txt.png"
cp "$FROM/mimetypes/vcalendar.png" "$TO/ics.png"
cp "$FROM/mimetypes/vcard.png" "$TO/vcf.png"
cp "$FROM/mimetypes/video.png" "$TO/mpg.png"
cp "$FROM/mimetypes/ooo_sxw.png" "$TO/sxw.png"
#    log
TO="../log/$SIZE"
mkdir $TO
cp "$FROM/actions/2leftarrow.png" "$TO/copiedfrom.png"
cp "$FROM/actions/fileopen.png" "$TO/m.png"
cp "$FROM/actions/attach.png" "$TO/a.png"
cp "$FROM/actions/textblock.png" "$TO/message.png"
cp "$FROM/apps/db.png" "$TO/d.png"

