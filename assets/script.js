// assets/script.js

// Enable Bootstrap Tooltips (Optional, if you use them)
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});

// Copy to Clipboard Function
function copyToClipboard(btn, text) {
    if (!navigator.clipboard) {
        // Fallback for older browsers
        var textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("Copy");
        textArea.remove();
        showCopiedFeedback(btn);
        return;
    }

    navigator.clipboard.writeText(text).then(function() {
        showCopiedFeedback(btn);
    }, function(err) {
        console.error('Could not copy text: ', err);
        alert("Failed to copy payload.");
    });
}

function showCopiedFeedback(btn) {
    const originalText = btn.innerText;
    const originalClass = btn.className;

    // Change button appearance
    btn.innerText = "Copied!";
    btn.classList.remove('btn-outline-primary', 'btn-copy');
    btn.classList.add('btn-success');

    // Reset after 2 seconds
    setTimeout(() => {
        btn.innerText = originalText;
        btn.className = originalClass; // Revert classes
    }, 2000);
}