import CorePages from '@/core/pages';
import AdminPages from '@/orangehrmAdminPlugin';
import PimPages from '@/orangehrmPimPlugin';
import HelpPages from '@/orangehrmHelpPlugin';
import TimePages from '@/orangehrmTimePlugin';
import LeavePages from '@/orangehrmLeavePlugin';
import OAuthPages from '@/orangehrmCoreOAuthPlugin';
import AttendancePages from '@/orangehrmAttendancePlugin';
import MaintenancePages from '@/orangehrmMaintenancePlugin';
import RecruitmentPages from '@/orangehrmRecruitmentPlugin';
import PerformancePages from '@/orangehrmPerformancePlugin';
import CorporateDirectoryPages from '@/orangehrmCorporateDirectoryPlugin';
import authenticationPages from '@/orangehrmAuthenticationPlugin';
import languagePages from '@/orangehrmAdminPlugin';
import dashboardPages from '@/orangehrmDashboardPlugin';
import buzzPages from '@/orangehrmBuzzPlugin';
import systemCheckPages from '@/orangehrmSystemCheckPlugin';
import claimPages from '@/orangehrmClaimPlugin';

export default {
  ...AdminPages,
  ...PimPages,
  ...CorePages,
  ...HelpPages,
  ...TimePages,
  ...OAuthPages,
  ...LeavePages,
  ...AttendancePages,
  ...MaintenancePages,
  ...RecruitmentPages,
  ...PerformancePages,
  ...CorporateDirectoryPages,
  ...authenticationPages,
  ...languagePages,
  ...dashboardPages,
  ...buzzPages,
  ...systemCheckPages,
  ...claimPages,
};
