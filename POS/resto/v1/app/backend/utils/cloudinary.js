
const cloudinary = require('cloudinary').v2;
require('dotenv').config();

cloudinary.config({
 cloud_name: 'dpbyxdhtv',
  api_key: '991824198121826',
  api_secret: 'BrUKsO2TyY6eKCeE-FUn-xnhNQE',
});

module.exports = cloudinary;
