(function () {
  if ( document.querySelector('.mp-row-checkout-pix') !== null ) {

    var detailsPixSize = document.querySelector('.mp-details-pix');

    if( detailsPixSize.offsetWidth <= 700 ) {
      document.querySelector('.mp-col-md-4').style.width = '100%';
      document.querySelector('.mp-col-md-4').style.flex = 'none';

      document.querySelector('.mp-col-md-8').style.width = '100%';
      document.querySelector('.mp-col-md-8').style.flex = 'none';

      document.querySelector('.mp-pix-right').style.borderRight = 'none';
      document.querySelector('.mp-pix-right').style.borderTop = 'solid 1px #e5e5e5';
      document.querySelector('.mp-pix-right').style.marginBittom = '32px';

      document.querySelector('.mp-qr-input').style.width = '100%';
      document.querySelector('.mp-qr-input').style.margin = '0 0 24px 0';

      document.querySelector('.mp-details-pix-button').style.width = '100%';
      document.querySelector('.mp-details-pix-button').style.height = 'auto';
      document.querySelector('.mp-details-pix-button').style.display = 'block';

      document.querySelector('.mp-row-checkout-pix-container').style.display = 'block';

      document.querySelector('.mp-details-pix-img').style.margin = '64px auto 0';
      document.querySelector('.mp-details-pix-img').style.padding = '0px';

      document.querySelector('.mp-details-pix-title').style.margin = '40px auto 0';
      document.querySelector('.mp-details-pix-title').style.textAlign = 'center';
      document.querySelector('.mp-details-pix-title').style.padding = '0px';
    }
	}
})();
