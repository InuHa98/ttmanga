var Config = {
  urlGetListImage: "",
  useDirectDownloadLink: true,
  idListContainer: "",
  classChapterItem: "",
  attrId: "",
  attrName: "",
  iconHtml: "",
  color: {
    ready: "#ff9600",
    loading: "#00a2ff",
    paused: "red",
    complete: "green",
    error: "red",
  },
  text: {
    download: "Tải xuống",
    pause: "Dừng tải",
    complete: "Hoàn thành",
    error: "Lỗi",
    titleError: "Cấu hình tải xuống không chính xác.",
    viewOnline: "Xem chương này online",
    titleDownload: "Nhấn để bắt đầu tải xuống.",
    titlePause: "Nhấn để dừng tải xuống.",
    titleResume: "Nhấn để tiếp tục tải xuống.",
    titleComplete: "Chương này đã tải xong.",
  },
};

if (typeof Cf_Download !== "undefined") {
  for (let key in Cf_Download) Config[key] = Cf_Download[key];
}

(function ($) {
  let totalChapterCount, chapterElements, downloadManager;

  function initChaptersRecursive(index) {
    if (index >= totalChapterCount) return;
    initializeChapter(chapterElements[index], index);
    requestAnimationFrame(() => initChaptersRecursive(++index));
  }

  function initializeChapter(chapterElement, chapterIndex) {
    const relatedElement = document.getElementById(
      Config.classChapterItem + "-" + chapterElement.getAttribute(Config.attrId)
    );

    if (relatedElement) {
      downloadManager.addChapter(
        chapterIndex,
        chapterElement.getAttribute(Config.attrName),
        chapterElement.getAttribute(Config.attrId),
        relatedElement.getAttribute("href"),
        relatedElement.innerHTML
      );
    } else {
      downloadManager.addChapter(
        chapterIndex,
        chapterElement.getAttribute(Config.attrName),
        chapterElement.getAttribute(Config.attrId),
        "",
        ""
      );
    }

    if (chapterElement.innerHTML.trim() !== "")
      downloadManager.addDownloadLink(
        chapterIndex,
        chapterElement.innerHTML.trim()
      );

    chapterElement.innerHTML = `<font color='${Config.color.ready}'>${Config.text.download}</font>`;
    chapterElement.title = Config.text.titleDownload;
    chapterElement.style.cursor = "pointer";

    chapterElement.addEventListener("click", function () {
      if (
        Config.useDirectDownloadLink &&
        downloadManager.chapterData[chapterIndex].Link
      )
        window.open(downloadManager.chapterData[chapterIndex].Link, "_blank");
      else {
        const status = downloadManager.chapterData[chapterIndex].Status;
        status === "ready"
          ? downloadManager.startDownload(chapterIndex)
          : downloadManager.togglePause(chapterIndex);
      }
    });
  }

  function getResponseContent(response) {
    return response.response || response.responseText;
  }

  function createXHR() {
    try {
      return new XMLHttpRequest();
    } catch {
      try {
        return new ActiveXObject("Microsoft.XMLHTTP");
      } catch {
        return null;
      }
    }
  }

  if (
    ("URL" in window || "webkitURL" in window) &&
    "JSZip" in window &&
    "saveAs" in window
  ) {
    class DownloadManager {
      constructor() {
        this.chapterData = [];
        this.errorOccurred = false;
        this.activeXHRs = [];
      }

      addError() {
        this.errorOccurred = true;
      }

      addChapter(index, name, id, url, fullName) {
        this.chapterData[index] = {
          Name: name,
          Id: id,
          Url: url,
          FullName: fullName,
          ImageUrls: [],
          Progress: 0,
          Complete: 0,
          Total: 0,
          Link: 0,
          Status: "ready",
          zip: new JSZip(),
        };
      }

      addDownloadLink(index, link) {
        this.chapterData[index].Link = link;
      }

      updateStatus(index, status) {
        this.chapterData[index].Status = status;
      }

      pauseChapter(index) {
        const xhrs = this.activeXHRs[index] || [];
        for (let xhr of xhrs) xhr.abort();
      }

      resumeChapter(index) {
        const chapter = this.chapterData[index];
        chapter.ImageUrls.forEach((url, i) => this.addToZip(index, url, i));
      }

      saveChapter(index) {
        const chapter = this.chapterData[index];
        this.addHtmlIndexFile(index);

        const zipBlob = chapter.zip.generate({ type: "blob" });
        const chapterElement = chapterElements[index];

        chapterElement.innerHTML = `<font color='${Config.color.complete}'>${
          Config.text.complete
        }</font> <a download='${
          chapter.Name
        }.zip' href='${window.URL.createObjectURL(zipBlob)}'>${
          Config.iconHtml
        }</a>`;
        chapterElement.title = Config.text.titleComplete;
        chapterElement.style.cursor = "not-allowed";

        saveAs(zipBlob, `${chapter.Name}.zip`);
      }

      addHtmlIndexFile(index) {
        const chapter = this.chapterData[index];
        let html =
          `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${chapter.FullName}</title>` +
          `<style>body{background:#2b2b2b;font-family:sans-serif;color:#fff;text-align:center}` +
          `img{width:100%}</style></head><body><h2>${chapter.FullName}</h2>`;
        for (let i = 0; i < chapter.ImageUrls.length; i++)
          html += `<img src="${chapter.Name}/${i}.jpg" />`;
        html += `<p><a href="${chapter.Url}" target="_blank">${Config.text.viewOnline}</a></p></body></html>`;
        chapter.zip.file(`${chapter.Name}.html`, html);
      }

      addToZip(index, imageUrl, imageIndex) {
        const chapter = this.chapterData[index];
        if (!chapter.ImageUrls[imageIndex])
          chapter.ImageUrls[imageIndex] = imageUrl;

        const folder = chapter.zip.folder(chapter.Name);
        const xhr = createXHR();

        xhr.open("GET", imageUrl, true);
        xhr.responseType = "arraybuffer";

        xhr.onerror = () => this._handleImageError(index, folder, imageIndex);
        xhr.onload = () =>
          this._handleImageLoad(index, xhr, folder, imageIndex);
        xhr.send();

        this.activeXHRs[index] ||= [];
        this.activeXHRs[index].push(xhr);
      }

      _handleImageError(index, folder, imageIndex) {
        const chapter = this.chapterData[index];
        folder.file(`${imageIndex}.jpg`, "");
        chapter.Progress++;
        this._checkComplete(index);
      }

      _handleImageLoad(index, xhr, folder, imageIndex) {
        const chapter = this.chapterData[index];
        if (xhr.status === 200 || xhr.status === 0)
          folder.file(`${imageIndex}.jpg`, getResponseContent(xhr), {
            binary: true,
          });
        else folder.file(`${imageIndex}.jpg`, "");

        chapter.Progress++;
        chapterElements[
          index
        ].innerHTML = `<font color='${Config.color.loading}'>${chapter.Progress}/${chapter.Total}</font>`;
        chapterElements[index].title = Config.text.titlePause;
        chapterElements[index].style.cursor = "pointer";

        this._checkComplete(index);
      }

      _checkComplete(index) {
        const chapter = this.chapterData[index];
        if (chapter.Progress === chapter.Total) {
          chapter.Complete = 1;
          this.saveChapter(index);
        }
      }

      startDownload(index) {
        const chapter = this.chapterData[index];
        chapterElements[
          index
        ].innerHTML = `<font color='${Config.color.loading}'>0/0</font>`;
        this.updateStatus(index, "load");

        $.ajax({
          async: true,
          type: "POST",
          url: Config.urlGetListImage,
          data: { id: chapter.Id },
          dataType: "json",
        })
          .done((response) => {
            response = response?.data || [];
            chapter.Total = response.length;
            response.forEach((url, i) => this.addToZip(index, url, i));
          })
          .fail(() => {
            this.addError();
            chapterElements[
              index
            ].innerHTML = `<font color='${Config.color.error}'>${Config.text.error}</font>`;
            chapterElements[index].title = Config.text.titleError;
          });
      }

      togglePause(index) {
        const chapter = this.chapterData[index];
        if (this.errorOccurred) return;

        if (chapter.Status === "load" && !chapter.Complete) {
          chapterElements[
            index
          ].innerHTML = `<font color='${Config.color.paused}'>${Config.text.pause}</font>`;
          chapterElements[index].title = Config.text.titleResume;
          this.updateStatus(index, "pause");
          this.pauseChapter(index);
        } else if (chapter.Status === "pause" && !chapter.Complete) {
          chapterElements[
            index
          ].innerHTML = `<font color='${Config.color.loading}'>${chapter.Progress}/${chapter.Total}</font>`;
          chapterElements[index].title = Config.text.titlePause;
          this.updateStatus(index, "load");
          this.resumeChapter(index);
        }
      }
    }

    document.addEventListener("DOMContentLoaded", function () {
      window.URL = window.URL || window.webkitURL;
      chapterElements = document
        .getElementById(Config.idListContainer)
        .getElementsByClassName(Config.classChapterItem);
      totalChapterCount = chapterElements.length;
      downloadManager = new DownloadManager();
      initChaptersRecursive(0);
    });
  }
})(jQuery);
