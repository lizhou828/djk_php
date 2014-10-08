$(function () {
    init('copy-description');
});

function init(btnid) {
    var clip = null;
    ZeroClipboard.setMoviePath("ZeroClipboard.swf");
    clip = new ZeroClipboard.Client();
    clip.setHandCursor(true);
    clip.setCSSEffects(true);

    clip.addEventListener('mouseDown', function (client) {
        clip.setText($("#text1").html());
    });

    clip.addEventListener("mouseOver", function(client) {
        client.setText($("#text1").html()); // 重新设置要复制的值
    });

    clip.addEventListener('complete', function (client, text) {
        alert("复制成功！");
    });

    clip.glue(btnid);
}

