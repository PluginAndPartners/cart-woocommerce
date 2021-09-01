const fs = require('fs');
const path = require('path');
const minify = require('minify');
const wpPot = require('wp-pot');

function minifyFiles(extension) {
    const assetsPath = path.resolve(`./assets/${extension}`);
    const assetsFiles = fs.readdirSync(assetsPath);
    const isNotMinifiedAndHasSelectedExtension = filePath => filePath.includes(`.${extension}`) && !filePath.includes('.min');
    const filtredFiles = assetsFiles.filter(filePath => isNotMinifiedAndHasSelectedExtension(filePath));

    filtredFiles.forEach(file => {
        const filePath = path.resolve(`${assetsPath}/${file}`);
    
        minify(filePath, {js: {ecma: 6}, css: {compatibility: '*'}})
            .then(minifiedContent => {
                const newFilePathName = filePath.split(`.${extension}`)[0].concat(`.min.${extension}`);
                fs.writeFileSync(newFilePathName, minifiedContent);
            })
            .catch(console.error);
    });
}

function generatePotFiles() {
    wpPot({
        destFile: './i18n/languages/woocommerce-mercadopago.pot',
        domain: 'woocommerce-mercadopago',
        lastTranslator: 'MPB Desenvolvimento <mpb_desenvolvimento@mercadopago.com.br>',
        src: '**/*.php'
      });
}

module.exports = {minifyFiles, generatePotFiles};