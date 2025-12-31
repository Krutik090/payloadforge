// assets/script.js
function copyToClipboard(elementId) {
    const copyText = document.getElementById(elementId);
    if (!copyText) return;

    // Use the clipboard API
    navigator.clipboard.writeText(copyText.value).then(() => {
        alert("Payload copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
        // Fallback for older environments
        copyText.select();
        document.execCommand("copy");
        alert("Payload copied!");
    });
}