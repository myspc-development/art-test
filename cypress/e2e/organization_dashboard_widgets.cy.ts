import fs from 'fs';
import path from 'path';

interface WidgetSets {
  allowed: string[];
  unauthorized: string[];
}

function getWidgetsForRole(role: 'member' | 'artist' | 'organization'): WidgetSets {
  const docPath = path.join(Cypress.config('projectRoot'), 'docs', 'qa', 'dashboard-widgets-by-role.md');
  const content = fs.readFileSync(docPath, 'utf8');
  const lines = content.split('\n');
  const start = lines.findIndex((l) => l.startsWith('| Widget ID'));
  const roleIndex: Record<typeof role, number> = {
    member: 3,
    artist: 4,
    organization: 5,
  } as const;
  const allowed: string[] = [];
  const unauthorized: string[] = [];
  for (let i = start + 2; i < lines.length; i++) {
    const line = lines[i];
    if (!line.startsWith('|')) break;
    const cells = line.split('|').map((c) => c.trim());
    const id = cells[1];
    const mark = cells[roleIndex[role]];
    if (!id) continue;
    if (mark === '✅') {
      allowed.push(id);
    } else if (mark === '❌') {
      unauthorized.push(id);
    }
  }
  return { allowed, unauthorized };
}

const { allowed, unauthorized } = getWidgetsForRole('organization');

describe('Organization Dashboard Widgets', () => {
  beforeEach(() => {
    cy.login('organization');
    cy.visit('/dashboard');
  });

  allowed.forEach((id) => {
    it(`renders widget ${id}`, () => {
      cy.get(`[data-widget-id="${id}"]`, { timeout: 15000 }).should('exist');
    });
  });

  unauthorized.forEach((id) => {
    it(`does not render unauthorized widget ${id}`, () => {
      cy.get(`[data-widget-id="${id}"]`).should('not.exist');
    });
  });
});

