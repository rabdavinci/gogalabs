const express = require('express');
const cors = require('cors');
const nodemailer = require('nodemailer');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');

const app = express();
const PORT = process.env.PORT || 3000;

// Security middleware
app.use(helmet());

// Rate limiting - max 5 requests per 15 minutes per IP
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 5, // limit each IP to 5 requests per windowMs
  message: {
    error: 'Слишком много запросов. Попробуйте позже.'
  }
});

app.use('/api/contact', limiter);

// CORS configuration
app.use(cors({
  origin: ['https://gogalabs.com', 'https://www.gogalabs.com', 'http://localhost'],
  methods: ['POST'],
  credentials: true
}));

// Body parsing middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Email configuration
const transporter = nodemailer.createTransporter({
  service: 'gmail',
  auth: {
    user: process.env.EMAIL_USER || 'usmonovgayrat89@gmail.com',
    pass: process.env.EMAIL_PASS // App password from Gmail
  }
});

// Verify email configuration
transporter.verify((error, success) => {
  if (error) {
    console.log('Email configuration error:', error);
  } else {
    console.log('Email server ready to send messages');
  }
});

// Contact form endpoint
app.post('/api/contact', async (req, res) => {
  try {
    const { name, email, message } = req.body;

    // Basic validation
    if (!name || !email || !message) {
      return res.status(400).json({
        success: false,
        error: 'Все поля обязательны для заполнения'
      });
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return res.status(400).json({
        success: false,
        error: 'Неверный формат email'
      });
    }

    // Prepare email content
    const mailOptions = {
      from: process.env.EMAIL_USER || 'usmonovgayrat89@gmail.com',
      to: 'usmonovgayrat89@gmail.com',
      subject: `Новое сообщение с сайта GoGaLabs от ${name}`,
      html: `
        <h2>Новое сообщение с контактной формы GoGaLabs</h2>
        <p><strong>Имя:</strong> ${name}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Сообщение:</strong></p>
        <p>${message.replace(/\n/g, '<br>')}</p>
        <hr>
        <p><em>Отправлено: ${new Date().toLocaleString('ru-RU')}</em></p>
      `,
      replyTo: email
    };

    // Send email
    await transporter.sendMail(mailOptions);

    console.log(`Email sent from ${email} (${name})`);

    res.json({
      success: true,
      message: 'Сообщение успешно отправлено!'
    });

  } catch (error) {
    console.error('Error sending email:', error);
    res.status(500).json({
      success: false,
      error: 'Ошибка отправки сообщения. Попробуйте позже.'
    });
  }
});

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

// Start server
app.listen(PORT, () => {
  console.log(`GoGaLabs backend server running on port ${PORT}`);
});

module.exports = app;