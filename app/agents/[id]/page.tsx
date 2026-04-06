export default async function AgentDetail({ params }: { params: Promise<{ id: string }> }) { const { id } = await params; return <main className="p-8">Agent ID: {id}</main>; }
