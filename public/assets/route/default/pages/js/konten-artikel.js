const quill = new Quill('#randerer', {
    readOnly: true
});

// render the content
quill.setContents(JSON.parse(document.querySelector('#randerer').innerText));

// Get all share buttons
const shareButtons = document.querySelectorAll('.share button.medsos-icon');

shareButtons.forEach(button => {
    button.addEventListener('click', (e) => {
         let uTarget = window.location.href; 
         // Get the URL of the current page
         const url = uTarget;
  
         // Get the social media platform from the button's class name
         const platform = button.children[0].classList[1];
 
         // Set the URL to share based on the social media platform
         let shareUrl;
         
         switch (platform) {
             //  case 'facebook':
             //  shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
             //  break;
             case 'bi-twitter-x':
             shareUrl = `https://twitter.com/share?url=${encodeURIComponent(url)}`;
             break;
             //  case 'linkedin':
             //  shareUrl = `https://www.linkedin.com/shareArticle?url=${encodeURIComponent(url)}`;
             //  break;
             case 'lni-whatsapp':
             shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`;
             break;
         }
 
        // Open a new window to share the URL
        window.open(shareUrl, '_blank');
    });
 });
