const http = require('http');
const socketio = require('socket.io');
const jwt = require('jsonwebtoken');
const APDebug = require('../js/APDebug');

// JWTs must include an expiration (`exp`) claim which will be validated on
// each connection.
const SECRET = process.env.JWT_SECRET;
if (!SECRET) {
  console.error('JWT_SECRET environment variable is required');
  process.exit(1);
}
if (SECRET.length < 10) {
  console.error('JWT_SECRET must be at least 10 characters long');
  process.exit(1);
}
const server = http.createServer();
const io = socketio(server, { cors: { origin: '*' } });

const connections = new Map();

io.use((socket, next) => {
  const token = socket.handshake.auth && socket.handshake.auth.token;
  if (!token) return next(new Error('unauthorized'));
  try {
    const payload = jwt.verify(token, SECRET, { algorithms: ['HS256'] });
    if (!payload.exp) {
      return next(new Error('missing exp claim'));
    }
    socket.userId = payload.user_id;
    return next();
  } catch (err) {
    if (err.name === 'TokenExpiredError') {
      return next(new Error('token expired'));
    }
    return next(new Error('unauthorized'));
  }
});

io.on('connection', socket => {
  connections.set(socket.userId, socket);
  APDebug.log('connection', socket.userId);

  socket.on('message:send', data => {
    const recipient = connections.get(data.recipient_id);
    if (recipient) {
      recipient.emit('message:new', { type: 'message:new', data });
    }
  });

  socket.on('message:seen', ids => {
    socket.broadcast.emit('message:seen', ids);
  });

  socket.on('user:typing', data => {
    socket.broadcast.emit('user:typing', data);
  });

  socket.on('disconnect', () => {
    connections.delete(socket.userId);
    APDebug.log('disconnect', socket.userId);
  });
});

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
  APDebug.log('ws server running on', PORT);
});
