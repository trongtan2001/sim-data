jQuery(document).ready(function ($) {
  // Thêm text header vào popup gtranslate
  if ($(".gt_white_content .gt_languages").length > 0) {
    $(".gt_white_content .gt_languages").before(
      '<div class="gt_header_text">Ngôn ngữ</div>'
    );
    $(".gt_white_content .gt_languages").before(
      '<div class="gt_search_wrapper">' +
        '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class=" Icon_main__igGXX"><path d="M12.0026 12.0026C14.0947 9.91054 14.0947 6.51859 12.0026 4.42649C9.91054 2.3344 6.51859 2.3344 4.42649 4.42649C2.3344 6.51859 2.3344 9.91054 4.42649 12.0026C6.51859 14.0947 9.91054 14.0947 12.0026 12.0026ZM12.0026 12.0026L15.7146 15.7146" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"></path></svg><input type="text" class="gt_language_search" placeholder="Tìm kiếm">' +
        "</div>"
    );
    $(".gt_languages a").each(function () {
      $(this).append('<div class="language-dot"></div>');
    });
    // Xử lý tìm kiếm
    $(".gt_language_search").on("keyup", function () {
      var searchText = $(this).val().toLowerCase();

      $(".gt_languages a").each(function () {
        var languageName = $(this).find("span").text().toLowerCase();

        if (languageName.indexOf(searchText) > -1) {
          $(this).attr("style", "display: flex !important;");
        } else {
          $(this).attr("style", "display: none !important;");
        }
      });

      // Hiển thị thông báo nếu không tìm thấy kết quả
      if ($(".gt_languages a:visible").length === 0) {
        if ($(".gt_no_results").length === 0) {
          $(".gt_languages").append(
            '<div class="gt_no_results">Không tìm thấy ngôn ngữ</div>'
          );
        }
      } else {
        $(".gt_no_results").remove();
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  function updateSwitcher() {
    const switcher = document.querySelector(".gt_switcher-popup");
    if (switcher) {
      const img = switcher.querySelector("img");
      const langSpan = switcher.querySelector("span:first-of-type");
      if (img && langSpan) {
        langSpan.textContent = img.getAttribute("alt");
      }

      const arrowSpan = switcher.querySelector('span[style*="color:#666"]');
      if (arrowSpan && !arrowSpan.querySelector("svg")) {
        arrowSpan.innerHTML =
          '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="Icon_main__igGXX" style="vertical-align: middle;"><path d="M13.7148 5.71429L8.00056 11.4286L2.28627 5.71429" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
      }
    }
  }

  // Chạy lần đầu
  updateSwitcher();

  // Lắng nghe click trên các link ngôn ngữ
  document.addEventListener("click", function (e) {
    const langLink = e.target.closest("a.glink[data-gt-lang]");
    if (langLink) {
      // Chờ một chút để GTranslate cập nhật xong
      setTimeout(updateSwitcher, 50);
      setTimeout(updateSwitcher, 200);
      setTimeout(updateSwitcher, 500);
    }
  });
});
