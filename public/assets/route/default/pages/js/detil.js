const counterTarget = document.querySelectorAll('.box-info [data-target].countUp:not(.sisa-waktu)'),
      counterTargetDate = document.querySelectorAll('.box-info [data-target].countUp.sisa-waktu'),
      counterSpeed = 2000;

counterUpSup(counterTarget, counterSpeed);

counterUpSup(counterTargetDate, counterSpeed, true);

let progressBar = document.querySelectorAll('.bantuan-area .progress-bar');
counterUpProgress(progressBar, counterSpeed);
