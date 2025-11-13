//go:build js && wasm
package main

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"syscall/js"
	"time"
	"regexp"
	"strings"
)

func trim(this js.Value, args []js.Value) any {

	// console := js.Global().Get("console")

	if len(args) == 0 {
		return nil
	}
	oldUrl := args[0].String()
	// Lấy host từ 
	href := js.Global().Get("window").Get("location").Get("href").String()
	href = strings.Split(href, "#")[0]
	re := regexp.MustCompile(`^(.*)/([0-9]+)\?(.*?)$`)
	host := re.ReplaceAllString(href, `$1/$2`)
	now := time.Now().Unix()
	sum := md5.Sum([]byte(fmt.Sprintf("%s---%d", host, now)))
	hash := hex.EncodeToString(sum[:])

	// console.Call("log", host)

	// Cắt và thay phần /image/
	start := findIndex(oldUrl, "/image/")
	if start < 0 || findIndex(oldUrl, "/TTmb") < 0 {
		return oldUrl
	}

	// Lấy toàn bộ phần sau /image/
	parts := strings.SplitN(oldUrl[start+7:], "/", 3) // tách thành 3 phần: hash cũ, time cũ, filename
	if len(parts) < 3 {
		return oldUrl
	}
	filename := parts[2] // phần filename thực sự

	return fmt.Sprintf("%s/image/%s/%d/%s", oldUrl[:start], hash, now, filename)
}

func findIndex(s, sub string) int {
	for i := range s {
		if len(s[i:]) >= len(sub) && s[i:i+len(sub)] == sub {
			return i
		}
	}
	return -1
}

func main() {
	js.Global().Set("trim", js.FuncOf(trim))
	select {}
}