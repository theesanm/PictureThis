// Completely minimal profile page for testing
export const dynamic = 'force-dynamic';

export default function ProfilePage() {
  return (
    <div className="p-6">
      <h1 className="text-3xl font-bold mb-6 text-white">Profile (minimal)</h1>
      <p className="text-gray-400">This is a minimal profile page used to test the build.</p>
    </div>
  );
}
