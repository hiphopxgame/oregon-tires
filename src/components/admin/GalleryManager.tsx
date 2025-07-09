import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Plus, Edit2, Save, X, Trash2, Upload, Image as ImageIcon } from 'lucide-react';
import { useGalleryImages, GalleryImage } from '@/hooks/useGalleryImages';

export const GalleryManager = () => {
  const { images, loading, uploadImage, addImage, updateImage, deleteImage } = useGalleryImages();
  const [showAddForm, setShowAddForm] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editingData, setEditingData] = useState<Partial<GalleryImage>>({});
  const [newImage, setNewImage] = useState({
    title: '',
    description: '',
    language: 'english',
    display_order: 0,
    is_active: true
  });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file && file.type.startsWith('image/')) {
      setSelectedFile(file);
    }
  };

  const handleAddImage = async () => {
    if (!newImage.title.trim() || !selectedFile) return;

    setUploading(true);
    try {
      const imageUrl = await uploadImage(selectedFile);
      if (imageUrl) {
        await addImage({
          ...newImage,
          image_url: imageUrl
        });
        
        setNewImage({
          title: '',
          description: '',
          language: 'english',
          display_order: 0,
          is_active: true
        });
        setSelectedFile(null);
        setShowAddForm(false);
      }
    } finally {
      setUploading(false);
    }
  };

  const handleUpdateImage = async (id: string, updates: Partial<GalleryImage>) => {
    await updateImage(id, updates);
    setEditingId(null);
    setEditingData({});
  };

  const startEditing = (image: GalleryImage) => {
    setEditingId(image.id);
    setEditingData({
      title: image.title,
      description: image.description,
      language: image.language,
      display_order: image.display_order,
      is_active: image.is_active
    });
  };

  const cancelEditing = () => {
    setEditingId(null);
    setEditingData({});
  };

  if (loading) {
    return <div className="text-green-700">Loading gallery images...</div>;
  }

  return (
    <Card className="border-2 border-green-700">
      <CardHeader className="bg-green-700 text-white">
        <div className="flex items-center justify-between">
          <CardTitle>Gallery Management</CardTitle>
          <Button 
            variant="secondary" 
            size="sm"
            onClick={() => setShowAddForm(!showAddForm)}
          >
            <Plus className="h-4 w-4 mr-1" />
            Add Image
          </Button>
        </div>
      </CardHeader>
      <CardContent className="p-4">
        {showAddForm && (
          <div className="mb-6 p-4 border rounded-lg bg-gray-50">
            <h4 className="font-medium mb-3">Add New Image</h4>
            <div className="space-y-3">
              <div>
                <Label htmlFor="image-upload">Image File *</Label>
                <div className="flex items-center gap-2">
                  <Input
                    id="image-upload"
                    type="file"
                    accept="image/*"
                    onChange={handleFileSelect}
                    className="flex-1"
                  />
                  {selectedFile && <ImageIcon className="h-4 w-4 text-green-600" />}
                </div>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <Label htmlFor="title">Title *</Label>
                  <Input
                    id="title"
                    value={newImage.title}
                    onChange={(e) => setNewImage(prev => ({ ...prev, title: e.target.value }))}
                    placeholder="Image title"
                  />
                </div>
                <div>
                  <Label htmlFor="language">Language *</Label>
                  <Select 
                    value={newImage.language} 
                    onValueChange={(value) => setNewImage(prev => ({ ...prev, language: value }))}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="english">English</SelectItem>
                      <SelectItem value="spanish">Spanish</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div>
                <Label htmlFor="description">Description</Label>
                <Textarea
                  id="description"
                  value={newImage.description}
                  onChange={(e) => setNewImage(prev => ({ ...prev, description: e.target.value }))}
                  placeholder="Image description"
                  rows={2}
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <Label htmlFor="display_order">Display Order</Label>
                  <Input
                    id="display_order"
                    type="number"
                    value={newImage.display_order}
                    onChange={(e) => setNewImage(prev => ({ ...prev, display_order: parseInt(e.target.value) || 0 }))}
                  />
                </div>
                <div className="flex items-center space-x-2">
                  <Switch
                    id="is_active"
                    checked={newImage.is_active}
                    onCheckedChange={(checked) => setNewImage(prev => ({ ...prev, is_active: checked }))}
                  />
                  <Label htmlFor="is_active">Active</Label>
                </div>
              </div>
            </div>
            <div className="flex gap-2 mt-3">
              <Button 
                onClick={handleAddImage} 
                size="sm" 
                disabled={uploading || !newImage.title.trim() || !selectedFile}
              >
                {uploading ? <Upload className="h-4 w-4 mr-1 animate-spin" /> : <Save className="h-4 w-4 mr-1" />}
                {uploading ? 'Uploading...' : 'Save'}
              </Button>
              <Button 
                variant="outline" 
                size="sm"
                onClick={() => {
                  setShowAddForm(false);
                  setNewImage({
                    title: '',
                    description: '',
                    language: 'english',
                    display_order: 0,
                    is_active: true
                  });
                  setSelectedFile(null);
                }}
              >
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>
          </div>
        )}

        <div className="space-y-3">
          {images.map((image) => (
            <div 
              key={image.id} 
              className="border rounded-lg bg-white p-3"
            >
              {editingId === image.id ? (
                <div className="space-y-3">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                      <Label>Title</Label>
                      <Input
                        value={editingData.title || ''}
                        onChange={(e) => setEditingData(prev => ({ ...prev, title: e.target.value }))}
                      />
                    </div>
                    <div>
                      <Label>Language</Label>
                      <Select 
                        value={editingData.language} 
                        onValueChange={(value) => setEditingData(prev => ({ ...prev, language: value }))}
                      >
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="english">English</SelectItem>
                          <SelectItem value="spanish">Spanish</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div>
                    <Label>Description</Label>
                    <Textarea
                      value={editingData.description || ''}
                      onChange={(e) => setEditingData(prev => ({ ...prev, description: e.target.value }))}
                      rows={2}
                    />
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div>
                      <Label>Display Order</Label>
                      <Input
                        type="number"
                        value={editingData.display_order || 0}
                        onChange={(e) => setEditingData(prev => ({ ...prev, display_order: parseInt(e.target.value) || 0 }))}
                      />
                    </div>
                    <div className="flex items-center space-x-2">
                      <Switch
                        checked={editingData.is_active ?? true}
                        onCheckedChange={(checked) => setEditingData(prev => ({ ...prev, is_active: checked }))}
                      />
                      <Label>Active</Label>
                    </div>
                    <div className="flex gap-2">
                      <Button 
                        size="sm" 
                        onClick={() => handleUpdateImage(image.id, editingData)}
                      >
                        <Save className="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={cancelEditing}
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="flex items-start gap-4">
                  <img 
                    src={image.image_url} 
                    alt={image.title}
                    className="w-20 h-20 object-cover rounded"
                  />
                  <div className="flex-1">
                    <div className="flex items-start justify-between">
                      <div>
                        <h4 className="font-medium">{image.title}</h4>
                        <p className="text-sm text-gray-600 capitalize">
                          {image.language} • Order: {image.display_order} • {image.is_active ? 'Active' : 'Inactive'}
                        </p>
                        {image.description && (
                          <p className="text-sm text-gray-500 mt-1">{image.description}</p>
                        )}
                      </div>
                      <div className="flex items-center gap-2">
                        <Button 
                          variant="outline" 
                          size="sm"
                          onClick={() => startEditing(image)}
                        >
                          <Edit2 className="h-4 w-4" />
                        </Button>
                        <Button 
                          variant="destructive" 
                          size="sm"
                          onClick={() => deleteImage(image.id, image.image_url)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>

        {images.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            No gallery images found. Add your first image above.
          </div>
        )}
      </CardContent>
    </Card>
  );
};