#!/bin/bash

echo "🔍 Server Health Check Script"
echo "=============================="

# Check backend
echo "🔧 Backend (port 3011):"
if curl -s http://localhost:3011/api/health >/dev/null 2>&1; then
    echo "✅ Backend is running and healthy"
else
    echo "❌ Backend is not responding"
    echo "   To restart: cd /Volumes/MacM4Ext/Projects/PictureThis/PictureThis/backend/src && node server.js &"
fi

echo ""

# Check frontend
echo "🌐 Frontend (port 3010):"
if curl -s http://localhost:3010 >/dev/null 2>&1; then
    echo "✅ Frontend is running and accessible"
else
    echo "❌ Frontend is not responding"
    echo "   To restart: cd /Volumes/MacM4Ext/Projects/PictureThis/PictureThis/frontend && npm run dev"
fi

echo ""

# Show running processes
echo "🔄 Running processes:"
ps aux | grep -E "(node server\.js|vite)" | grep -v grep | while read line; do
    echo "   $line"
done

echo ""
echo "🌍 Access URLs:"
echo "   Frontend: http://localhost:3010"
echo "   Backend:  http://localhost:3011"
echo "   API Health: http://localhost:3011/api/health"
