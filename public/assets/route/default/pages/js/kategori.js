const kategoriColor = document.querySelector('section.kategori-area'),
      elResumeHeader = document.querySelector('#resume-kategori .text-kategori');

elResumeHeader.style.color = getComputedStyle(kategoriColor).getPropertyValue('--kategori-color');

const resumeElList = document.querySelectorAll('#resume-kategori [data-count-up-value]'),
      counterSpeed = 3000;

counterUpSup(resumeElList, counterSpeed);