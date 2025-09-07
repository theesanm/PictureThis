import React, { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { imagesAPI } from '../utils/api'
import { Image, Plus, Download, CreditCard, Sparkles } from 'lucide-react'
import toast from 'react-hot-toast'

const Dashboard = () => {
  const { user, credits } = useAuth()
  const [images, setImages] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadImages()
  }, [])

  const loadImages = async () => {
    try {
      const response = await imagesAPI.getMyImages()
      setImages(response.data.data.images)
    } catch (error) {
      console.error('Failed to load images:', error)
      toast.error('Failed to load images')
    } finally {
      setLoading(false)
    }
  }

  const handleDownload = async (imageId, fileName) => {
    try {
      const response = await imagesAPI.download(imageId)
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', fileName || 'generated-image.jpg')
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      toast.success('Image downloaded!')
    } catch (error) {
      console.error('Download failed:', error)
      toast.error('Download failed')
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-7xl mx-auto">
        {/* Welcome Section */}
        <div className="bg-slate-800 rounded-lg shadow-sm p-6 mb-8 border border-slate-700">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-100">
              Welcome back, {user?.fullName}!
            </h1>
            <p className="text-slate-300 mt-1">
              Ready to create something amazing?
            </p>
          </div>
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2 bg-slate-700 px-4 py-2 rounded-lg border border-slate-600">
              <CreditCard className="h-5 w-5 text-blue-400" />
              <span className="font-medium text-blue-400">{credits} credits</span>
            </div>
            <Link
              to="/generate"
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
            >
              <Sparkles className="h-4 w-4" />
              <span>Generate New</span>
            </Link>
          </div>
        </div>
      </div>

      {/* Recent Images */}
      <div className="bg-slate-800 rounded-lg shadow-sm p-6 border border-slate-700">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-slate-100">Your Images</h2>
          <Link
            to="/generate"
            className="text-blue-400 hover:text-blue-300 flex items-center space-x-1"
          >
            <Plus className="h-4 w-4" />
            <span>Create New</span>
          </Link>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : images.length === 0 ? (
          <div className="text-center py-12">
            <Image className="h-16 w-16 text-slate-400 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-slate-100 mb-2">
              No images yet
            </h3>
            <p className="text-slate-300 mb-4">
              Start creating your first AI-generated image!
            </p>
            <Link
              to="/generate"
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center space-x-2"
            >
              <Sparkles className="h-4 w-4" />
              <span>Generate Your First Image</span>
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {images.map((image) => (
              <div key={image.id} className="bg-slate-700 rounded-lg overflow-hidden border border-slate-600">
                <div className="aspect-square bg-slate-600 flex items-center justify-center">
                  {image.image_url ? (
                    <img
                      src={image.image_url}
                      alt={image.prompt}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <Image className="h-12 w-12 text-slate-400" />
                  )}
                </div>
                <div className="p-4">
                  <p className="text-sm text-slate-300 mb-2 line-clamp-2">
                    {image.prompt}
                  </p>
                  <div className="flex items-center justify-between">
                    <span className="text-xs text-slate-400">
                      {new Date(image.created_at).toLocaleDateString()}
                    </span>
                    <button
                      onClick={() => handleDownload(image.id, image.file_name)}
                      className="text-blue-400 hover:text-blue-300 p-1"
                      title="Download"
                    >
                      <Download className="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
    </div>
  )
}

export default Dashboard
