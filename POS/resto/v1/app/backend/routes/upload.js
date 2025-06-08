const express = require('express');
const multer = require('multer');
const fs = require('fs');
const cloudinary = require('../utils/cloudinary');

const router = express.Router();
const upload = multer({ dest: 'uploads/' });

router.post('/upload', upload.single('file'), async (req, res) => {
  try {
    const result = await cloudinary.uploader.upload(req.file.path, {
      folder: 'resto-pos',
      transformation: [
        {
          width: 300,
          height: 300,
          crop: 'fill',
          gravity: 'auto', // or 'faces' to focus on faces
        },
        {
          fetch_format: 'auto',
          quality: 'auto',
        },
      ],
    });

    fs.unlinkSync(req.file.path); // Remove temp file after upload
    res.json({ url: result.secure_url });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Upload failed' });
  }
});


module.exports = router;
