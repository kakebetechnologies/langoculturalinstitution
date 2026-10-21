// Event tickets (the code you get when you register) are kept on the phone, so they can be shown at the door with no internet.
import { readJson, writeJson } from './localFiles';

const FILE = 'tickets.json';

export async function loadTickets() {
  return (await readJson(FILE)) ?? [];
}

export async function saveTicket(ticket) {
  const all = (await loadTickets()).filter((t) => t.code !== ticket.code);
  all.unshift({ ...ticket, at: Date.now() });
  await writeJson(FILE, all.slice(0, 50));
}

export async function ticketsFor(slug) {
  return (await loadTickets()).filter((t) => t.slug === slug);
}
