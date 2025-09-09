import GenerateClient from './GenerateClient';

export const dynamic = 'force-dynamic';
export const fetchCache = 'force-no-store';

export default function Page() {
  return <GenerateClient />;
}

